import tempfile
import unittest
from pathlib import Path
import numpy as np
from service import Store, best_match, normalized, Worker


class AttendanceTests(unittest.TestCase):
    def test_unknown_and_ambiguous_faces_never_match(self):
        face = normalized([1, 0, 0])
        self.assertEqual(best_match(face, [dict(id=1, feature=normalized([0, 1, 0]))])[0], None)
        self.assertEqual(best_match(face, [dict(id=1, feature=face), dict(id=2, feature=normalized([1, .05, 0]))])[0], None)
        self.assertEqual(best_match(face, [dict(id=1, feature=face)])[0], 1)

    def test_duplicates_and_restart(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / 'test.sqlite3'
            store = Store(path)
            student = dict(id=1, name='Local test', roll='TEST')
            store.mark('a', student, .7); store.mark('a', student, .8)
            store.mark('b', student, .8)
            store.enroll(3, student, normalized([1, 2, 3]))
            config = store.config(); config.update(active=True, class_id=3); store.save(config)
            store.db.close()
            reopened = Store(path)
            self.assertEqual(reopened.db.execute('SELECT count(*) FROM events').fetchone()[0], 2)
            self.assertTrue(reopened.config()['active'])
            self.assertEqual(len(reopened.profiles(3)), 1)
            self.assertEqual(reopened.profiles(4), [])
            reopened.db.close()

    def test_blank_frames_and_commands(self):
        with tempfile.TemporaryDirectory() as directory:
            worker = Worker(Path(directory))
            worker.command('start', dict(class_id=0, source='0', roster=[dict(id=1, name='Test', roll='TEST')]))
            session = worker.config['session']
            worker.process(np.zeros((480, 640, 3), dtype=np.uint8))
            self.assertEqual(worker.status()['events'], [])
            self.assertRaises(ValueError, worker.command, 'enroll', {'student_id': 1})
            self.assertRaises(ValueError, worker.command, 'start', {'source': '0'})
            worker.command('stop', {})
            worker.command('start', dict(class_id=0, source='0', roster=[dict(id=1, name='Test', roll='TEST')]))
            self.assertEqual(worker.config['session'], session)
            worker.config['session_date'] = '2000-01-01'
            worker.rollover()
            self.assertNotEqual(worker.config['session'], session)
            daily_session = worker.config['session']
            worker.rollover()
            self.assertEqual(worker.config['session'], daily_session)
            worker.command('new_session', {})
            self.assertNotEqual(worker.config['session'], daily_session)
            worker.command('stop', {})
            self.assertFalse(worker.status()['active'])
            self.assertIsNone(worker.status(True)['preview'])
            worker.store.db.close()


if __name__ == '__main__':
    unittest.main()
