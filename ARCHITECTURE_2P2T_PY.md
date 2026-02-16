# مخطط معماري محدث لتطبيق 2P2T بلغة Python

> الهدف: تحويل الفكرة إلى تصميم عملي **بدون سيرفر مركزي**، بحيث أول اقتران يتم محليًا (Bluetooth/Nearby) ثم يصبح الاتصال لاحقًا دائمًا عبر Wi‑Fi/الإنترنت حتى لو الطرفان في دول مختلفة.

## 1) الفكرة الأساسية (بعد التعديل المطلوب)

- التطبيق يعمل بين **2 إلى 4 أجهزة فقط** داخل جلسة واحدة.
- **أول مرة فقط** يجب أن يكون الجهازان قريبين مكانيًا لإجراء اقتران موثوق عبر Bluetooth LE.
- أثناء الاقتران الأول يتم تبادل مادة الثقة (مفاتيح عامة + بصمات + رمز تحقق قصير).
- بعد نجاح الاقتران، كل جهاز يحتفظ فقط بـ **Trusted Peer Record** مشفر محليًا.
- في المرات التالية: لا حاجة للقرب المكاني، ويمكن الاتصال عبر Wi‑Fi/الإنترنت (P2P) مباشرة باستخدام المفاتيح الموثقة سابقًا.
- لا حسابات، لا تسجيل دخول، لا قاعدة بيانات مركزية.
- حذف كامل (messages/voice/session keys) عند إنهاء الجلسة.

---

## 2) Stack Python المقترح

- **لغة**: Python 3.11+
- **واجهة**:
  - Android: Kivy أو BeeWare (Toga) أو واجهة خفيفة مرتبطة بخدمة Python.
  - Windows: PySide6 (Qt) أو Toga.
- **التشفير**: `cryptography` (AES‑256‑GCM, RSA‑4096 أو X25519/Ed25519).
- **نقل P2P عبر الإنترنت**:
  - خيار 1: WebRTC data channel عبر `aiortc`.
  - خيار 2: libp2p bindings (إذا متاحة مستقرًا).
- **اكتشاف/اقتران قريب**: BLE (مثل `bleak`) + QR fallback.
- **الصوت**: `opuslib` أو دمج Opus encoder native + `sounddevice`/`pyaudio`.
- **التزامن**: `asyncio` + قنوات أحداث داخلية.

> ملاحظة عملية: في Python، WebRTC غالبًا أسهل وأوضح من libp2p من ناحية الاستقرار.

---

## 3) المعمارية المنطقية (Layers)

### A) طبقة UI/UX

- شاشة إنشاء/انضمام جلسة (PIN من 6 أرقام).
- شاشة الاقتران الأول (Nearby Pairing Required).
- شاشة محادثة نص + صوت.
- مؤشرات أمان: حالة القفل، حالة التوقيع، جودة الاتصال.
- زر "إنهاء الجلسة" = wipe فوري.

### B) طبقة الهوية والثقة Trust Layer

- إنشاء Device Identity محلي:
  - `device_id` عشوائي.
  - زوج مفاتيح طويل العمر للجهاز (Identity Key Pair).
- Trusted Peer Record لكل جهاز موثوق:
  - peer_id
  - public identity key
  - fingerprint
  - first_paired_at
  - last_seen
- هذا السجل **ليس رسالة ولا تاريخ محادثة**؛ فقط مادة ثقة لازمة لإعادة الاتصال الآمن.

### C) طبقة الاقتران الأول (Proximity Bootstrap)

- الشرط: الجهازان قريبان.
- خطوات مقترحة:
  1. Discover عبر BLE advertising.
  2. تبادل public identity keys.
  3. عرض SAS (Short Authentication String) من 6–8 رموز على الجهازين.
  4. المستخدم يؤكد التطابق يدويًا.
  5. حفظ الطرف الآخر كـ Trusted Peer.
- بديل: QR يحتوي fingerprint + ephemeral offer.

### D) طبقة الجلسة Session Layer

- عند بدء جلسة جديدة:
  - توليد Session Ephemeral Keys.
  - اشتقاق مفاتيح AES‑256‑GCM عبر HKDF.
  - عدادات sequence + nonce management.
- لكل جلسة:
  - مفتاح تشفير نص.
  - مفتاح تشفير صوت.
  - مفاتيح منفصلة للتوقيع/التحقق أو AEAD tags كافية.

### E) طبقة النقل Networking Layer

- Local first policy:
  - إذا نفس الشبكة: اتصال مباشر LAN/Wi‑Fi Direct.
- Remote fallback:
  - WebRTC P2P + ICE (STUN/TURN).
  - TURN يستخدم فقط كـ relay للمرور، بدون فك تشفير المحتوى.
- Reconnect logic:
  - إعادة محاولة ذكية مع exponential backoff.
  - المحافظة على session continuity مع rekey عند الانقطاع الطويل.

### F) طبقة الرسائل والصوت Media/Data Layer

- Text:
  - packet = header + ciphertext + auth tag + signature(optional).
- Voice:
  - PCM -> Opus -> AES‑GCM -> send chunks.
  - jitter buffer + packet loss concealment.
- رسائل ephemeral:
  - TTL لكل رسالة.
  - حذف تلقائي من الذاكرة عند انتهاء TTL.

### G) طبقة الأمان المحلي Runtime Security

- Android:
  - `FLAG_SECURE` لمنع screenshot.
- Windows:
  - صعب منع التسجيل 100%؛ نستخدم best effort (كشف أدوات شائعة + watermark + تنبيه + إنهاء جلسة اختياري).
- Memory hygiene:
  - مسح buffers الحساسة بعد الاستخدام.
  - عدم كتابة محتوى الرسائل للـ logs.

---

## 4) تسلسل التشغيل المطلوب (مرة أولى vs لاحقًا)

### السيناريو 1: أول اقتران (قرب مكاني)

1. A يفتح Pair New Device.
2. B يفتح Pair New Device.
3. BLE discovery وتبادل identity public keys.
4. عرض SAS على الجهازين.
5. إذا تطابق SAS، يتم اعتماد كل جهاز كـ Trusted Peer.
6. يمكن الآن إنشاء جلسة PIN مشفرة.

### السيناريو 2: اتصال لاحق عبر الإنترنت (بدون قرب)

1. A يختار peer موثوق سابقًا.
2. A/B يتبادلان offers/answers عبر قناة signaling خفيفة.
3. يتم التحقق من هوية الطرف عبر المفتاح المخزن مسبقًا.
4. إنشاء قناة P2P مشفرة end‑to‑end.
5. بدء نص/صوت مع rotation دوري للمفاتيح.

> signaling يمكن أن يكون عبر عدة وسائل (حتى channel بسيط/مؤقت)، لكن الأمان لا يعتمد عليه لأن التحقق يتم بالمفاتيح الموثقة من الاقتران الأول.

---

## 5) البروتوكول الأمني المقترح

- التشفير: AES‑256‑GCM (بدل CBC لتفادي أخطاء النزاهة).
- تبادل مفاتيح:
  - وفق طلبك: RSA‑4096 لتشفير session material.
  - مقترح أقوى عمليًا: X25519 (ECDH) + Ed25519 للتوقيع.
- Forward Secrecy:
  - مفاتيح ephemeral لكل جلسة + rekey كل N دقيقة/رسالة.
- Anti-replay:
  - session_id + monotonically increasing counter + نافذة قبول ضيقة.
- Anti-tamper:
  - AEAD tag + توقيع للهيدر الحرج.

---

## 6) بنية المشروع في Python

```text
2p2t/
  app/
    ui/
      screens.py
    core/
      session_manager.py
      peer_store.py
      message_router.py
    crypto/
      identity.py
      handshake.py
      aead.py
      ratchet.py
    transport/
      ble_pairing.py
      webrtc_channel.py
      lan_discovery.py
      turn_fallback.py
    media/
      audio_capture.py
      opus_pipeline.py
      voice_stream.py
    security/
      secure_wipe.py
      anti_capture_android.py
      anti_capture_windows.py
    storage/
      volatile_store.py
      trusted_peers_store.py
  tests/
    test_handshake.py
    test_replay_protection.py
    test_voice_pipeline.py
```

---

## 7) خطة التنفيذ (Python-first)

### Phase 1: Identity + Nearby Pairing
- توليد مفاتيح الهوية.
- BLE pairing + SAS confirmation.
- حفظ Trusted Peer Record مشفر.

### Phase 2: Secure Text
- Session handshake + AES‑GCM packets.
- PIN channel (2–4 أجهزة).
- TTL + auto-delete + secure wipe.

### Phase 3: Voice
- Opus encode/decode.
- encrypted audio frames.
- QoS metrics (latency/jitter/loss).

### Phase 4: Remote P2P
- WebRTC data/voice channels.
- STUN/TURN + reconnect/rekey.
- استمرار الاعتماد على مفاتيح الاقتران الأول.

### Phase 5: Hardening
- اختبارات اختراق داخلية.
- fuzzing لطبقة packet parser.
- قياس الأداء على Android/Windows.

---

## 8) قرارات تصميم مهمة للفريق

- **نعم**: أول مرة لازم قرب مكاني (Bluetooth + SAS) لبناء الثقة.
- **نعم**: بعد أول مرة الاتصال يصبح ممكن عالميًا عبر Wi‑Fi/Internet.
- **لا**: لا نثق بأي signaling server ككيان أمني.
- **نعم**: الاحتفاظ فقط بـ Trusted Peer Keys محليًا (مشفر) للسماح بإعادة الاتصال الآمن.
- **نعم**: كل محتوى المحادثة ephemeral ويُمسح نهائيًا عند الإنهاء.

