from __future__ import annotations

from datetime import datetime, timedelta, timezone
import json
from pathlib import Path
from uuid import uuid4

from flask import Flask, jsonify, request, send_from_directory

ROOT = Path(__file__).parent
USERS_FILE = ROOT / "users" / "users.json"
STORIES_FILE = ROOT / "stories" / "stories.json"
CHAT_FILE = ROOT / "chat" / "messages.json"

app = Flask(__name__, static_folder="static", static_url_path="/static")


def read_json(path: Path, default: dict) -> dict:
    if not path.exists():
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(json.dumps(default, ensure_ascii=False, indent=2), encoding="utf-8")
        return default
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return default


def write_json(path: Path, data: dict) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")


def cleanup_stories() -> list[dict]:
    payload = read_json(STORIES_FILE, {"stories": []})
    now = datetime.now(timezone.utc)
    valid = []
    for story in payload.get("stories", []):
        try:
            expires_at = datetime.fromisoformat(story["expires_at"])
            if expires_at.tzinfo is None:
                expires_at = expires_at.replace(tzinfo=timezone.utc)
            if expires_at > now:
                valid.append(story)
        except Exception:
            continue
    payload["stories"] = valid
    write_json(STORIES_FILE, payload)
    return valid


@app.route("/")
def home():
    return send_from_directory(ROOT, "index.html")


@app.route("/api/login", methods=["POST"])
def login():
    data = request.get_json(silent=True) or {}
    username = (data.get("username") or "").strip()
    if not username:
        return jsonify({"error": "Username is required"}), 400

    payload = read_json(USERS_FILE, {"users": []})
    users = payload.get("users", [])
    if username not in users:
        users.append(username)
        payload["users"] = users
        write_json(USERS_FILE, payload)

    return jsonify({"username": username, "online_count": len(users)})


@app.route("/api/users", methods=["GET"])
def users():
    payload = read_json(USERS_FILE, {"users": []})
    return jsonify({"users": payload.get("users", []), "online_count": len(payload.get("users", []))})


@app.route("/api/videos/tiktok", methods=["GET"])
def tiktok_videos():
    base = ROOT / "videos" / "tiktok"
    files = [f"/videos/tiktok/{p.name}" for p in sorted(base.glob("*")) if p.is_file()]
    return jsonify({"videos": files})


@app.route("/api/videos/youtube", methods=["GET"])
def youtube_videos():
    base = ROOT / "videos" / "youtube"
    results = []
    for playlist in sorted(base.iterdir() if base.exists() else []):
        if playlist.is_dir():
            videos = [f"/videos/youtube/{playlist.name}/{p.name}" for p in sorted(playlist.glob("*")) if p.is_file()]
            results.append({"playlist": playlist.name, "videos": videos})
    return jsonify({"playlists": results})


@app.route("/api/stories", methods=["GET", "POST"])
def stories():
    if request.method == "GET":
        valid = cleanup_stories()
        return jsonify({"stories": valid})

    data = request.get_json(silent=True) or {}
    username = (data.get("username") or "").strip() or "anonymous"
    media_url = (data.get("media_url") or "").strip()
    media_type = (data.get("media_type") or "image").strip()

    if not media_url:
        return jsonify({"error": "media_url is required"}), 400

    created = datetime.now(timezone.utc)
    expires = created + timedelta(hours=24)
    story = {
        "id": str(uuid4()),
        "username": username,
        "media_url": media_url,
        "media_type": media_type,
        "created_at": created.isoformat(),
        "expires_at": expires.isoformat(),
    }

    payload = read_json(STORIES_FILE, {"stories": []})
    payload.setdefault("stories", []).append(story)
    write_json(STORIES_FILE, payload)
    return jsonify(story), 201


@app.route("/api/chat/public", methods=["GET", "POST"])
def chat_public():
    payload = read_json(CHAT_FILE, {"public": [], "private": []})
    if request.method == "GET":
        return jsonify({"messages": payload.get("public", [])})

    data = request.get_json(silent=True) or {}
    username = (data.get("username") or "anonymous").strip()
    text = (data.get("text") or "").strip()
    voice_note = (data.get("voice_note") or "").strip()
    if not text and not voice_note:
        return jsonify({"error": "text or voice_note is required"}), 400

    message = {
        "id": str(uuid4()),
        "username": username,
        "text": text,
        "voice_note": voice_note,
        "timestamp": datetime.now(timezone.utc).isoformat(),
    }
    payload.setdefault("public", []).append(message)
    write_json(CHAT_FILE, payload)
    return jsonify(message), 201


@app.route("/api/chat/private", methods=["GET", "POST"])
def chat_private():
    payload = read_json(CHAT_FILE, {"public": [], "private": []})
    if request.method == "GET":
        me = (request.args.get("me") or "").strip()
        peer = (request.args.get("peer") or "").strip()
        if not me or not peer:
            return jsonify({"messages": []})
        msgs = [
            m for m in payload.get("private", [])
            if {m.get("from"), m.get("to")} == {me, peer}
        ]
        return jsonify({"messages": msgs})

    data = request.get_json(silent=True) or {}
    from_user = (data.get("from") or "").strip()
    to_user = (data.get("to") or "").strip()
    text = (data.get("text") or "").strip()
    voice_note = (data.get("voice_note") or "").strip()
    if not from_user or not to_user or (not text and not voice_note):
        return jsonify({"error": "from, to and text/voice_note required"}), 400

    message = {
        "id": str(uuid4()),
        "from": from_user,
        "to": to_user,
        "text": text,
        "voice_note": voice_note,
        "timestamp": datetime.now(timezone.utc).isoformat(),
    }
    payload.setdefault("private", []).append(message)
    write_json(CHAT_FILE, payload)
    return jsonify(message), 201


@app.route("/videos/<path:subpath>")
def serve_video(subpath: str):
    return send_from_directory(ROOT / "videos", subpath)


@app.route("/games/<path:subpath>")
def serve_games(subpath: str):
    return send_from_directory(ROOT / "games", subpath)


@app.route("/chat/<path:subpath>")
def serve_chat(subpath: str):
    return send_from_directory(ROOT / "chat", subpath)


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
