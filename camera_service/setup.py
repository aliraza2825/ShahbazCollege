"""Download the OpenCV Zoo models with Git LFS SHA-256 verification."""
import hashlib
import json
from pathlib import Path
import urllib.request

root = Path(__file__).resolve().parent / 'models'
root.mkdir(exist_ok=True)
repo = 'https://raw.githubusercontent.com/opencv/opencv_zoo/'
commit = '47534e27c9851bb1128ccc0102f1145e27f23f98'
models = [('face_detection_yunet', 'face_detection_yunet_2023mar.onnx', 'yunet.onnx'), ('face_recognition_sface', 'face_recognition_sface_2021dec.onnx', 'sface.onnx')]
manifest = {'commit': commit, 'models': []}
for folder, filename, local in models:
    relative = f'models/{folder}/{filename}'
    pointer = urllib.request.urlopen(repo + commit + '/' + relative).read().decode()
    digest = next(line.split('sha256:')[1] for line in pointer.splitlines() if line.startswith('oid sha256:'))
    expected_size = int(next(line.split()[1] for line in pointer.splitlines() if line.startswith('size ')))
    target = root / local
    if not target.exists() or hashlib.sha256(target.read_bytes()).hexdigest() != digest:
        print('Downloading', local, flush=True)
        raw = urllib.request.urlopen('https://media.githubusercontent.com/media/opencv/opencv_zoo/' + commit + '/' + relative, timeout=120).read()
        if len(raw) != expected_size or hashlib.sha256(raw).hexdigest() != digest: raise RuntimeError('Model checksum mismatch')
        target.write_bytes(raw)
    license_text = urllib.request.urlopen(repo + commit + f'/models/{folder}/LICENSE').read()
    (root / (folder + '-LICENSE')).write_bytes(license_text)
    manifest['models'].append({'file': local, 'sha256': digest, 'bytes': expected_size})
(root / 'manifest.json').write_text(json.dumps(manifest, indent=2))
print('Models verified.', flush=True)
