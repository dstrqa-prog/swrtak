from __future__ import annotations

import json
import time
import uuid
from pathlib import Path
from typing import Any

from flask import Flask, jsonify, request, send_from_directory
from werkzeug.utils import secure_filename

ROOT = Path(__file__).resolve().parent
USERS_FILE = ROOT / "users" / "users.json"
CHAT_FILE = ROOT / "chat" / "messages.json"
STORIES_FILE = ROOT / "stories" / "stories.json"
STORIES_DIR = ROOT / "stories"
VIDEOS_DIR = ROOT / "videos"

ALLOWED_MEDIA_EXT = {".mp4", ".webm", ".mov", ".mkv", ".mp3", ".ogg", ".wav", ".jpg", ".jpeg", ".png", ".gif"}

app = Flask(__name__, static_folder="static", static_url_path="/static")


def read_json(path: Path, fallback: Any) -> Any:
    if not path.exists():
        return fallback
    return json.loads(path.read_text(encoding="utf-8"))


def write_json(path: Path, payload: Any) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")


def list_media_files(path: Path) -> list[str]:
    if not path.exists():
        return []
    return sorted(
        [
            item.name
            for item in path.iterdir()
            if item.is_file() and item.suffix.lower() in ALLOWED_MEDIA_EXT
        ]
    )


def cleanup_stories() -> list[dict[str, Any]]:
    now = int(time.time())
    stories = read_json(STORIES_FILE, [])
    active = [item for item in stories if item.get("expires_at", 0) > now]

    removed = {item.get("filename") for item in stories if item not in active}
    for filename in removed:
        if not filename:
            continue
        file_path = STORIES_DIR / filename
        if file_path.exists() and file_path.is_file():
            file_path.unlink()

    if len(active) != len(stories):
        write_json(STORIES_FILE, active)

    return active


@app.get("/")
def index() -> Any:
    return send_from_directory(ROOT, "index.html")


@app.get("/<section>/index.html")
def module_page(section: str) -> Any:
    module_path = ROOT / section / "index.html"
    if module_path.exists():
        return send_from_directory(ROOT / section, "index.html")
    return {"error": "Page not found"}, 404




@app.get("/<section>/<path:filename>")
def module_asset(section: str, filename: str) -> Any:
    section_path = ROOT / section
    file_path = section_path / filename
    if section_path.exists() and file_path.exists() and file_path.is_file():
        return send_from_directory(section_path, filename)
    return {"error": "Asset not found"}, 404


@app.get("/api/bootstrap")
def bootstrap() -> Any:
    users = read_json(USERS_FILE, {"known_users": [], "active_users": []})
    stories = cleanup_stories()
    return jsonify(
        {
            "online_count": len(users.get("active_users", [])),
            "users": users,
            "stories": stories,
            "games": ["xo", "chess", "uno", "ludo", "domino", "4inrow", "tennis", "tugofwar"],
        }
    )


@app.post("/api/login")
def login() -> Any:
    payload = request.get_json(silent=True) or {}
    username = str(payload.get("username", "")).strip()
    if not username:
        return jsonify({"error": "Username is required"}), 400

    users = read_json(USERS_FILE, {"known_users": [], "active_users": []})
    if username not in users["known_users"]:
        users["known_users"].append(username)
    if username not in users["active_users"]:
        users["active_users"].append(username)

    write_json(USERS_FILE, users)
    return jsonify({"ok": True, "username": username, "online_count": len(users["active_users"])})


@app.post("/api/logout")
def logout() -> Any:
    payload = request.get_json(silent=True) or {}
    username = str(payload.get("username", "")).strip()
    users = read_json(USERS_FILE, {"known_users": [], "active_users": []})
    users["active_users"] = [name for name in users.get("active_users", []) if name != username]
    write_json(USERS_FILE, users)
    return jsonify({"ok": True, "online_count": len(users["active_users"])})


@app.get("/api/users/online")
def online_users() -> Any:
    users = read_json(USERS_FILE, {"known_users": [], "active_users": []})
    return jsonify({"online": users.get("active_users", []), "count": len(users.get("active_users", []))})


@app.get("/api/videos/tiktok")
def tiktok_videos() -> Any:
    files = list_media_files(VIDEOS_DIR / "tiktok")
    return jsonify({"videos": [f"/media/videos/tiktok/{name}" for name in files]})


@app.get("/api/videos/youtube")
def youtube_videos() -> Any:
    root = VIDEOS_DIR / "youtube"
    playlists: dict[str, list[str]] = {}
    if root.exists():
        for item in sorted(root.iterdir()):
            if item.is_dir():
                files = list_media_files(item)
                playlists[item.name] = [f"/media/videos/youtube/{item.name}/{name}" for name in files]
    return jsonify({"playlists": playlists})


@app.get("/api/chat/public")
def get_public_chat() -> Any:
    data = read_json(CHAT_FILE, {"public": [], "private": {}})
    return jsonify({"messages": data.get("public", [])[-100:]})


@app.post("/api/chat/public")
def post_public_chat() -> Any:
    data = read_json(CHAT_FILE, {"public": [], "private": {}})
    payload = request.get_json(silent=True) or {}
    message = {
        "id": uuid.uuid4().hex,
        "user": str(payload.get("user", "guest")),
        "text": str(payload.get("text", "")).strip(),
        "ts": int(time.time()),
        "audio": payload.get("audio"),
    }
    if not message["text"] and not message["audio"]:
        return jsonify({"error": "Empty message"}), 400
    data["public"].append(message)
    write_json(CHAT_FILE, data)
    return jsonify({"ok": True, "message": message})


@app.get("/api/chat/private")
def get_private_chat() -> Any:
    user = request.args.get("user", "").strip()
    target = request.args.get("target", "").strip()
    if not user or not target:
        return jsonify({"messages": []})
    key = "::".join(sorted([user, target]))
    data = read_json(CHAT_FILE, {"public": [], "private": {}})
    return jsonify({"messages": data.get("private", {}).get(key, [])[-100:]})


@app.post("/api/chat/private")
def post_private_chat() -> Any:
    data = read_json(CHAT_FILE, {"public": [], "private": {}})
    payload = request.get_json(silent=True) or {}
    user = str(payload.get("user", "")).strip()
    target = str(payload.get("target", "")).strip()
    if not user or not target:
        return jsonify({"error": "User and target are required"}), 400
    key = "::".join(sorted([user, target]))
    data.setdefault("private", {}).setdefault(key, []).append(
        {
            "id": uuid.uuid4().hex,
            "user": user,
            "target": target,
            "text": str(payload.get("text", "")).strip(),
            "ts": int(time.time()),
            "audio": payload.get("audio"),
        }
    )
    write_json(CHAT_FILE, data)
    return jsonify({"ok": True})


@app.post("/api/chat/audio")
def upload_chat_audio() -> Any:
    uploaded = request.files.get("audio")
    if not uploaded or not uploaded.filename:
        return jsonify({"error": "Audio file is required"}), 400

    extension = Path(uploaded.filename).suffix or ".webm"
    filename = secure_filename(f"chat_audio_{uuid.uuid4().hex}{extension}")
    destination = ROOT / "chat" / filename
    uploaded.save(destination)
    return jsonify({"ok": True, "path": f"/media/chat/{filename}"})


@app.get("/api/stories")
def get_stories() -> Any:
    stories = cleanup_stories()
    return jsonify({"stories": stories})


@app.post("/api/stories")
def post_story() -> Any:
    username = request.form.get("user", "").strip()
    uploaded = request.files.get("story")
    if not username:
        return jsonify({"error": "Username is required"}), 400
    if not uploaded or not uploaded.filename:
        return jsonify({"error": "Story media is required"}), 400

    extension = Path(uploaded.filename).suffix
    if extension.lower() not in ALLOWED_MEDIA_EXT:
        return jsonify({"error": "Unsupported file format"}), 400

    filename = secure_filename(f"story_{uuid.uuid4().hex}{extension}")
    uploaded.save(STORIES_DIR / filename)

    stories = cleanup_stories()
    now = int(time.time())
    story = {
        "id": uuid.uuid4().hex,
        "user": username,
        "filename": filename,
        "url": f"/media/stories/{filename}",
        "created_at": now,
        "expires_at": now + 24 * 60 * 60,
    }
    stories.append(story)
    write_json(STORIES_FILE, stories)
    return jsonify({"ok": True, "story": story})


@app.get("/media/<path:asset_path>")
def serve_media(asset_path: str) -> Any:
    full = ROOT / asset_path
    if not full.exists() or not full.is_file():
        return {"error": "File not found"}, 404
    return send_from_directory(full.parent, full.name)


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
