# Background attendance camera pilot

React POS: **Classes → Live Attendance → Background recognition**.

This is real local face matching with OpenCV YuNet + SFace, not the separate
simulated UI demo. Python owns the camera. Closing the portal or PHP server does
not stop recognition. The computer must remain powered on, awake, and logged in.
The macOS LaunchAgent starts at login, restarts a failed worker, and resumes an
active camera session from SQLite. Stop the worker camera in the portal to
persistently disable capture.

## Test now

1. Open `http://localhost:5173/classes/live-attendance` and sign in.
2. Select **Personal test**, enter your name, keep camera index **0**, and click
   **Start background camera**. Close browser-camera preview or other camera apps.
3. Allow macOS camera permission if prompted. Wait for the worker's live image.
4. Click **Enroll selected face**. Be alone, close to the camera, in good light.
   Five consistent, sufficiently sharp samples are required. Enrollment to another
   student's already registered face is rejected within the active class.
5. Step away briefly and return. After a five-second enrollment grace period and
   four consecutive matching frames, **Matched · test present** appears.
6. Click **New test session**, close the portal tab, and return to the camera.
   Reopen the portal: the background check-in should be saved. Repeated sightings
   do not duplicate the session's check-in. Export results as CSV if needed.
7. For a class pilot, stop the camera, choose an existing class, restart, and
   enroll each student individually under the correct roster entry.

## Scope and limitations

- Single local computer, single camera, single selected class at a time.
- Camera indexes 0–10; RTSP/network cameras are not connected in this pilot yet.
- One persisted test check-in per student per session. Active sessions roll over
  daily in Asia/Karachi. A new test session can also be started manually.
- Test records and face embeddings stay in the local worker SQLite database.
  **No official college attendance tables are modified.**
- No photo/video anti-spoofing or liveness. Do not use this pilot as unattended
  production attendance until liveness, actual classroom accuracy, timetable
  rules, and official attendance integration are validated.
- Cosine threshold 0.50, runner-up margin 0.08, 4-frame confirmation, face size
  minimum 80 pixels, and sharpness checks are starting parameters, not an
  accuracy guarantee. Multi-student classroom performance needs a real pilot.
- No video or face photos are stored; the latest annotated JPEG is held in memory
  for the preview. Face embeddings are sensitive and remain in a user-only
  directory. Stop the camera to delete a selected face enrollment in the UI.
- Backend proxy retains existing POS login/class access checks and accepts local
  requests only. Worker listens on loopback with a generated secret, never
  exposed in browser JavaScript. Deploying to shared/remote hosts requires a
  separate authenticated service design; do not expose port 8765 publicly.

## Installation and operation

From the application directory, using Python 3.12:

```sh
python3 -m venv camera_service/.venv
camera_service/.venv/bin/python -m pip install -r camera_service/requirements.txt
camera_service/.venv/bin/python camera_service/setup.py
camera_service/.venv/bin/python camera_service/install_service.py
```

`setup.py` uses a pinned OpenCV Zoo commit, verifies both model sizes/SHA-256 from
Git LFS pointers, and saves upstream licenses and a model manifest next to them.
The installed LaunchAgent is `~/Library/LaunchAgents/local.attendance.camera.plist`.

Private state: `~/Library/Application Support/AttendanceCamera/`

- `attendance.sqlite3`: enrollment, session config and test check-ins
- `service.token`: loopback API secret (never commit or share)
- `service.log`: startup/camera errors

PHP and Python must run as the same local user. Set `ATTENDANCE_CAMERA_DATA` in
both processes if overriding the private data directory. Keep it outside the
web document root. The existing PHP server and Vite frontend are still needed
when using the portal; recognition itself needs neither once started.

macOS camera permission: **System Settings → Privacy & Security → Camera**.
If permission was denied, allow the Python/host application shown there and
restart the worker. Camera connection retries every five seconds.

Reload after code changes by rerunning `install_service.py`. To uninstall the
login service without deleting saved data:

```sh
launchctl bootout gui/$(id -u)/local.attendance.camera
rm ~/Library/LaunchAgents/local.attendance.camera.plist
```

## Verification

```sh
camera_service/.venv/bin/python camera_service/test_service.py
php -l controllers/Classesapi.php
```

Tests cover ambiguous/unknown rejection, SQLite duplicate suppression and
persistence, blank-frame processing, start/stop, and daily/manual session reset.
Physical-camera enrollment and 20–30-person classroom accuracy require people
in front of the camera; automated model fixture tests do not establish either.

Official model/API reference:
https://docs.opencv.org/4.12.0/d0/dd4/tutorial_dnn_face.html
https://github.com/opencv/opencv_zoo
