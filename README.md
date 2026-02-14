# Local Social Entertainment Platform

مشروع Flask محلي يجمع:
- فيديوهات TikTok/YouTube من ملفات محلية.
- ألعاب متعددة (وحدة XO جاهزة + قوالب لباقي الألعاب).
- شات عام وخاص (نصي مع حقل ملاحظات صوتية كـ URL/placeholder).
- ستوري مؤقتة 24 ساعة.
- تسجيل دخول بسيط باسم المستخدم فقط.

## التشغيل

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install flask
python server.py
```

افتح: `http://localhost:5000`

## البنية

- `users/users.json`: المستخدمون.
- `videos/tiktok` و `videos/youtube/<playlist>`: الفيديوهات.
- `games/*`: كل لعبة في مجلد منفصل.
- `chat/*`: واجهة الشات.
- `stories/stories.json`: التخزين المؤقت للستوري.
- `static/*`: ملفات الواجهة الرئيسية.

## ملاحظات

- تنظيف القصص المنتهية يتم عند طلب `/api/stories`.
- تم تصميم المجلدات لتسهيل إضافة وحدات جديدة مستقبلًا.
