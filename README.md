# منصة محلية متعددة (Flask)

مشروع محلي يعمل على `localhost` لتجميع الفيديوهات، الألعاب، الشات، والستوري في واجهة واحدة متجاوبة.

## التشغيل

```bash
python -m venv .venv
source .venv/bin/activate
pip install flask
python server.py
```

ثم افتح:
- الصفحة الرئيسية: `http://localhost:5000/`
- الفيديوهات: `http://localhost:5000/videos/index.html`
- الألعاب: `http://localhost:5000/games/index.html`
- الشات: `http://localhost:5000/chat/index.html`
- الستوري: `http://localhost:5000/stories/index.html`

## الهيكلية

- `server.py`: API + تقديم الملفات.
- `users/users.json`: تخزين المستخدمين المعروفين والمتصلين.
- `videos/`: مكتبة الفيديوهات المحلية (TikTok + YouTube playlists).
- `games/`: وحدة الألعاب (حاليًا XO مع AI + قوالب لباقي الألعاب).
- `chat/`: الشات العام/الخاص والرسائل الصوتية القصيرة.
- `stories/`: ستوري مؤقتة 24 ساعة مع تنظيف تلقائي.
- `static/`: ملفات الواجهة المشتركة.

## ملاحظات

- يتم تسجيل الدخول باسم مستخدم فقط.
- كل البيانات مخزنة محليًا بصيغة JSON، ومناسبة للتطوير والتجربة على جهاز واحد.
