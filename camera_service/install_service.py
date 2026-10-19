"""Install/reload this worker as a per-user macOS LaunchAgent."""
import os
from pathlib import Path
import plistlib
import subprocess
import time

root = Path(__file__).resolve().parent
runtime = Path.home() / 'Library/Application Support/AttendanceCamera'
runtime.mkdir(parents=True, exist_ok=True, mode=0o700)
os.chmod(runtime, 0o700)
agent = Path.home() / 'Library/LaunchAgents/local.attendance.camera.plist'
agent.parent.mkdir(parents=True, exist_ok=True)
# Use the venv entry point, not its resolved base-Python symlink.
python = str(root / '.venv/bin/python')
config = {
    'Label': 'local.attendance.camera',
    'ProgramArguments': [python, str(root / 'service.py')],
    'WorkingDirectory': str(root),
    'EnvironmentVariables': {'ATTENDANCE_CAMERA_DATA': str(runtime), 'PYTHONUNBUFFERED': '1', 'TZ': 'Asia/Karachi'},
    'RunAtLoad': True, 'KeepAlive': True, 'ThrottleInterval': 10,
    'StandardOutPath': str(runtime / 'service.log'),
    'StandardErrorPath': str(runtime / 'service.log'),
    'ProcessType': 'Interactive',
}
agent.write_bytes(plistlib.dumps(config))
os.chmod(agent, 0o600)
domain = f'gui/{os.getuid()}'
subprocess.run(['launchctl', 'bootout', domain + '/local.attendance.camera'], capture_output=True)
for attempt in range(10):
    result = subprocess.run(['launchctl', 'bootstrap', domain, str(agent)], capture_output=True, text=True)
    if result.returncode == 0:
        break
    time.sleep(1)  # bootout can finish asynchronously
else:
    raise RuntimeError('Could not load camera service: ' + result.stderr.strip())
print('Background service installed. It starts at login and restarts after failures.')
print('Logs:', runtime / 'service.log')
