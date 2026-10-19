"""Local background camera pilot. Never writes official college attendance."""
import base64
import hmac
import json
import logging
import os
from pathlib import Path
import secrets
import signal
import sqlite3
import threading
import time
import uuid
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

import cv2
import numpy as np

ROOT = Path(__file__).resolve().parent
DATA = Path(os.environ.get('ATTENDANCE_CAMERA_DATA', str(Path.home() / 'Library/Application Support/AttendanceCamera')))
THRESHOLD = 0.50
MARGIN = 0.08
CONFIRMATIONS = 4
SAMPLES = 5


def normalized(feature):
    feature = np.asarray(feature, dtype=np.float32).flatten()
    return feature / max(float(np.linalg.norm(feature)), 1e-8)


def best_match(feature, profiles):
    scores = sorted(((float(np.dot(feature, p['feature'])), p['id']) for p in profiles), reverse=True)
    if not scores or scores[0][0] < THRESHOLD:
        return None, 0.0
    if len(scores) > 1 and scores[0][0] - scores[1][0] < MARGIN:
        return None, scores[0][0]
    return scores[0][1], scores[0][0]


class Store:
    def __init__(self, path):
        self.db = sqlite3.connect(path, check_same_thread=False)
        self.db.row_factory = sqlite3.Row
        self.db.executescript('''
          PRAGMA journal_mode=WAL;
          CREATE TABLE IF NOT EXISTS profiles (class_id INTEGER, student_id INTEGER, name TEXT, roll TEXT, feature TEXT, PRIMARY KEY(class_id, student_id));
          CREATE TABLE IF NOT EXISTS events (session TEXT, student_id INTEGER, name TEXT, roll TEXT, time TEXT, similarity REAL, PRIMARY KEY(session, student_id));
          CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT);
        ''')

    def config(self):
        row = self.db.execute("SELECT value FROM settings WHERE key='config'").fetchone()
        return json.loads(row[0]) if row else {'active': False, 'class_id': 0, 'class_name': 'Personal camera test', 'session': str(uuid.uuid4()), 'session_date': time.strftime('%Y-%m-%d'), 'source': '0', 'roster': []}

    def save(self, config):
        with self.db:
            self.db.execute("INSERT OR REPLACE INTO settings VALUES ('config', ?)", (json.dumps(config),))

    def profiles(self, class_id):
        return [{**dict(r), 'id': r['student_id'], 'feature': np.array(json.loads(r['feature']), dtype=np.float32)} for r in self.db.execute('SELECT * FROM profiles WHERE class_id=?', (class_id,))]

    def enroll(self, class_id, student, feature):
        with self.db:
            self.db.execute('INSERT OR REPLACE INTO profiles VALUES (?,?,?,?,?)', (class_id, student['id'], student['name'], student['roll'], json.dumps(feature.tolist())))

    def mark(self, session, student, score):
        with self.db:
            self.db.execute('INSERT OR IGNORE INTO events VALUES (?,?,?,?,?,?)', (session, student['id'], student['name'], student['roll'], time.strftime('%Y-%m-%dT%H:%M:%S%z'), round(score, 4)))


class Worker:
    def __init__(self, data=DATA, models=None):
        data.mkdir(parents=True, exist_ok=True, mode=0o700)
        self.lock = threading.RLock()
        self.frame_lock = threading.Lock()
        self.latest = None
        self.frame_sequence = 0
        self.overlays = (0, [])
        self.capture_fps = 0.0
        self.store = Store(data / 'attendance.sqlite3')
        self.config = self.store.config()
        self.config.setdefault('session_date', time.strftime('%Y-%m-%d'))
        self.quit = threading.Event()
        self.camera_status = 'stopped'
        self.error = ''
        self.jpeg = None
        self.last_frame = 0
        self.job = None
        self.counts = {}
        self.generation = 0
        self.processed_frames = 0
        self.model_dir = models or ROOT / 'models'
        self.detector = cv2.FaceDetectorYN.create(str(self.model_dir / 'yunet.onnx'), '', (640, 480), 0.9, 0.3, 5000)
        self.recognizer = cv2.FaceRecognizerSF.create(str(self.model_dir / 'sface.onnx'), '')

    def status(self, preview=False):
        with self.lock:
            profiles = self.store.profiles(self.config['class_id'])
            events = [dict(r) for r in self.store.db.execute('SELECT * FROM events WHERE session=? ORDER BY time DESC', (self.config['session'],))]
            return {'success': True, 'active': self.config['active'], 'class_id': self.config['class_id'], 'class_name': self.config['class_name'], 'session': self.config['session'], 'camera_status': self.camera_status, 'error': self.error, 'enrolled_ids': [p['id'] for p in profiles], 'events': events, 'roster': self.config['roster'], 'processed_frames': self.processed_frames, 'capture_fps': self.capture_fps, 'last_frame_age': round(time.time() - self.last_frame, 1) if self.last_frame else None, 'enrollment': {k: v for k, v in self.job.items() if k not in ('samples', 'deadline')} if self.job else None, 'preview': base64.b64encode(self.jpeg).decode() if preview and self.jpeg and time.time() - self.last_frame < 4 else None, 'mode': 'local-test', 'liveness': False}

    def command(self, action, body):
        with self.lock:
            if action == 'start':
                if self.config['active']:
                    raise ValueError('Stop the current camera session before starting another.')
                source = str(body.get('source', '0')).strip()
                if not (source.isdigit() and int(source) <= 10):
                    raise ValueError('For this laptop pilot, camera index must be between 0 and 10.')
                class_id = int(body.get('class_id', 0))
                roster = body.get('roster', [])
                if not roster or len(roster) > 300:
                    raise ValueError('Select a class with students or use the personal test.')
                if class_id != self.config['class_id'] or {s['id'] for s in roster} != {s['id'] for s in self.config['roster']} or body.get('new_session'):
                    self.config['session'] = str(uuid.uuid4())
                self.config.update(active=True, class_id=class_id, class_name=str(body.get('class_name', 'Camera test')), source=source, roster=roster)
                self.error = ''; self.camera_status = 'connecting'; self.counts.clear(); self.job = None; self.generation += 1
                self.store.save(self.config)
            elif action == 'stop':
                self.config['active'] = False
                self.store.save(self.config)
                self.generation += 1; self.job = None; self.counts.clear(); self.camera_status = 'stopped'
                with self.frame_lock:
                    self.jpeg = None; self.latest = None; self.overlays = (0, [])
            elif action == 'new_session':
                self.config['session'] = str(uuid.uuid4()); self.config['session_date'] = time.strftime('%Y-%m-%d'); self.counts.clear()
                self.store.save(self.config)
            elif action == 'enroll':
                if not self.config['active'] or self.camera_status != 'connected':
                    raise ValueError('Start the background camera and wait for a live image first.')
                if self.job and self.job['status'] == 'capturing':
                    raise ValueError('An enrollment is already in progress.')
                student = next((s for s in self.config['roster'] if s['id'] == int(body.get('student_id', -1))), None)
                if not student:
                    raise ValueError('Student is not in the active roster.')
                self.job = {'student_id': student['id'], 'name': student['name'], 'status': 'capturing', 'message': 'Look at the camera alone. Hold still in good lighting.', 'captured': 0, 'samples': [], 'deadline': time.time() + 40}
                self.counts.clear()
            elif action == 'forget':
                if self.config['active']:
                    raise ValueError('Stop the camera before deleting enrollment.')
                with self.store.db:
                    self.store.db.execute('DELETE FROM profiles WHERE class_id=? AND student_id=?', (self.config['class_id'], int(body.get('student_id', -1))))
            else:
                raise ValueError('Unknown action.')
        return self.status()

    def process(self, frame):
        height, width = frame.shape[:2]
        if width > 960:
            frame = cv2.resize(frame, (960, int(height * 960 / width)))
        self.detector.setInputSize((frame.shape[1], frame.shape[0]))
        _, faces = self.detector.detect(frame)
        faces = [] if faces is None else faces
        profiles = self.store.profiles(self.config['class_id'])
        roster = {s['id']: s for s in self.config['roster']}
        profiles = [p for p in profiles if p['id'] in roster]
        seen = {}
        overlays = []
        capturing = self.job and self.job['status'] == 'capturing'
        if capturing and time.time() > self.job['deadline']:
            self.job.update(status='failed', message='Enrollment timed out. Check lighting and keep one face in view.')
            capturing = False
        for face in faces:
            x, y, w, h = map(int, face[:4])
            label, color = 'Unknown', (80, 170, 255)
            crop = frame[max(0, y):max(0, y) + h, max(0, x):max(0, x) + w]
            quality = min(w, h) >= 80 and crop.size and cv2.Laplacian(cv2.cvtColor(crop, cv2.COLOR_BGR2GRAY), cv2.CV_64F).var() >= 45
            if not quality:
                label = 'Move closer / improve lighting'
            else:
                aligned = self.recognizer.alignCrop(frame, face)
                feature = normalized(self.recognizer.feature(aligned))
                if capturing:
                    if len(faces) != 1:
                        self.job['message'] = 'Only the person being enrolled should be visible.'
                    else:
                        other_id, _ = best_match(feature, [p for p in profiles if p['id'] != self.job['student_id']])
                        if other_id is not None:
                            self.job.update(status='failed', message='This face is already enrolled to another student. Check the selected student.')
                            capturing = False
                        elif self.job['samples'] and float(np.dot(self.job['samples'][0], feature)) < THRESHOLD:
                            self.job.update(status='failed', message='Face changed during enrollment. Retry with one person.')
                            capturing = False
                        else:
                            self.job['samples'].append(feature)
                            self.job['captured'] = len(self.job['samples'])
                            label = f"Enrolling {self.job['captured']}/{SAMPLES}"
                            self.job['message'] = label
                            if self.job['captured'] >= SAMPLES:
                                self.store.enroll(self.config['class_id'], roster[self.job['student_id']], normalized(np.mean(self.job['samples'], axis=0)))
                                self.job.update(status='complete', recognize_after=time.time() + 5, message='Face enrolled. Step away, then return to test recognition.')
                                # Separate enrollment from recognition; new session can test repeat check-in.
                                capturing = False
                elif not (self.job and self.job['status'] == 'complete' and time.time() < self.job.get('recognize_after', 0)):
                    student_id, score = best_match(feature, profiles)
                    if student_id is not None:
                        seen[student_id] = score
                        label = roster[student_id]['name']
                        color = (110, 220, 80)
            overlays.append((x, y, w, h, label[:40], color))
        if capturing:
            self.counts.clear()
            if len(faces) == 0:
                self.job['message'] = 'No face found. Look towards the camera in good lighting.'
        else:
            self.counts = {sid: self.counts.get(sid, 0) + 1 for sid in seen}
            for sid, count in self.counts.items():
                if count >= CONFIRMATIONS:
                    self.store.mark(self.config['session'], roster[sid], seen[sid])
        self.overlays = (time.monotonic(), overlays)
        self.processed_frames += 1

    def frame(self):
        # Preview never waits for recognition or a SQLite query.
        with self.frame_lock:
            fresh = self.config['active'] and self.jpeg and time.time() - self.last_frame < 1.5
            return {'success': True, 'class_id': self.config['class_id'], 'sequence': self.frame_sequence,
                    'jpeg': base64.b64encode(self.jpeg).decode() if fresh else None}

    def recognize(self):
        sequence = -1
        while not self.quit.is_set():
            with self.frame_lock:
                latest = self.latest
            if latest is not None and latest[0] != sequence:
                sequence, generation, frame = latest
                try:
                    with self.lock:
                        if self.config['active'] and generation == self.generation:
                            self.process(frame)
                except Exception:
                    logging.exception('Recognition failed')
                    with self.lock:
                        self.error = 'Face processing failed. Check the service log.'
                        self.counts.clear()
            self.quit.wait(.20)

    def rollover(self):
        with self.lock:
            today = time.strftime('%Y-%m-%d')
            if self.config['active'] and self.config['session_date'] != today:
                self.config['session'] = str(uuid.uuid4())
                self.config['session_date'] = today
                self.counts.clear()
                self.store.save(self.config)

    def run(self):
        capture = None
        generation = -1
        measured_at = time.monotonic()
        frames = 0
        encoded_at = 0
        recognition = threading.Thread(target=self.recognize, daemon=True)
        recognition.start()
        try:
            while not self.quit.is_set():
                # Capture must drain the camera continuously; recognition runs separately.
                active, current, source = self.config['active'], self.generation, int(self.config['source'])
                if generation != current or not active:
                    if capture is not None:
                        capture.release(); capture = None
                    generation = current
                if not active:
                    self.quit.wait(.1); continue
                try:
                    if capture is None:
                        self.camera_status = 'connecting'
                        capture = cv2.VideoCapture(source)
                        capture.set(cv2.CAP_PROP_FRAME_WIDTH, 1280)
                        capture.set(cv2.CAP_PROP_FRAME_HEIGHT, 720)
                        capture.set(cv2.CAP_PROP_FPS, 30)
                        capture.set(cv2.CAP_PROP_BUFFERSIZE, 1)
                    ok, frame = capture.read()
                    if not ok:
                        raise RuntimeError('Camera unavailable. Allow camera access in macOS Settings and close other camera apps. Retrying in 5 seconds.')
                    if generation != self.generation or not self.config['active']:
                        continue
                    if frame.shape[1] > 960:
                        frame = cv2.resize(frame, (960, round(frame.shape[0] * 960 / frame.shape[1])))
                    now = time.monotonic()
                    with self.frame_lock:
                        self.frame_sequence += 1
                        self.latest = (self.frame_sequence, generation, frame)
                        self.last_frame = time.time()
                    if now - encoded_at >= 1 / 20:
                        display = frame.copy()
                        stamp, boxes = self.overlays
                        if now - stamp < .45:
                            for x, y, w, h, label, color in boxes:
                                cv2.rectangle(display, (x, y), (x + w, y + h), color, 2)
                                cv2.rectangle(display, (x, max(0, y - 24)), (min(display.shape[1], x + len(label) * 9 + 12), y), (20, 30, 40), -1)
                                cv2.putText(display, label, (max(0, x + 5), max(18, y - 7)), cv2.FONT_HERSHEY_SIMPLEX, .45, color, 1, cv2.LINE_AA)
                        ok, jpeg = cv2.imencode('.jpg', display, [cv2.IMWRITE_JPEG_QUALITY, 82])
                        if ok:
                            with self.frame_lock:
                                if generation == self.generation and self.config['active']:
                                    self.jpeg = jpeg.tobytes()
                        encoded_at = now
                    self.camera_status = 'connected'
                    frames += 1
                    if now - measured_at >= 2:
                        self.capture_fps = round(frames / (now - measured_at), 1)
                        frames = 0; measured_at = now
                        # Daily rollover uses the state lock, but not on every captured frame.
                        self.rollover()
                    self.error = ''
                except Exception as exc:
                    logging.exception('Camera capture failed')
                    if capture is not None: capture.release(); capture = None
                    with self.frame_lock:
                        self.jpeg = None; self.latest = None; self.overlays = (0, [])
                    with self.lock:
                        if generation == self.generation and self.config['active']:
                            self.camera_status = 'reconnecting'; self.error = str(exc); self.counts.clear()
                    self.quit.wait(5)
        finally:
            if capture is not None: capture.release()
            recognition.join(timeout=5)


def main():
    os.umask(0o077)
    DATA.mkdir(parents=True, exist_ok=True, mode=0o700)
    token_file = DATA / 'service.token'
    if not token_file.exists():
        token_file.write_text(secrets.token_hex(32))
    token = token_file.read_text().strip()
    worker = Worker()

    class Handler(BaseHTTPRequestHandler):
        def log_message(self, *_args): pass

        def respond(self, data, status=200):
            raw = json.dumps(data).encode()
            self.send_response(status); self.send_header('Content-Type', 'application/json')
            self.send_header('Cache-Control', 'no-store'); self.send_header('Content-Length', str(len(raw))); self.end_headers()
            self.wfile.write(raw)

        def do_POST(self):
            if not hmac.compare_digest(self.headers.get('X-Camera-Key', ''), token):
                self.respond({'success': False, 'message': 'Unauthorized'}, 401); return
            try:
                size = int(self.headers.get('Content-Length', '0'))
                if size < 0 or size > 150000:
                    raise ValueError('Request too large.')
                body = json.loads(self.rfile.read(size) or '{}')
                if not isinstance(body, dict): raise ValueError('Invalid request.')
                action = self.path.strip('/')
                if action == 'frame': self.respond(worker.frame())
                elif action == 'status': self.respond(worker.status(bool(body.get('preview'))))
                else: self.respond(worker.command(action, body))
            except (ValueError, TypeError, KeyError) as exc:
                self.respond({'success': False, 'message': str(exc)}, 422)
            except Exception:
                logging.exception('Request failed')
                self.respond({'success': False, 'message': 'Camera service error; check local service log.'}, 500)

    server = ThreadingHTTPServer(('127.0.0.1', 8765), Handler)
    server.daemon_threads = True
    thread = threading.Thread(target=worker.run, daemon=True); thread.start()
    def stop(_signum, _frame):
        worker.quit.set()
        threading.Thread(target=server.shutdown, daemon=True).start()
    signal.signal(signal.SIGTERM, stop); signal.signal(signal.SIGINT, stop)
    logging.warning('Attendance camera service listening on 127.0.0.1:8765 (local test records only)')
    try: server.serve_forever()
    finally:
        worker.quit.set(); thread.join(timeout=10); server.server_close()


if __name__ == '__main__':
    main()
