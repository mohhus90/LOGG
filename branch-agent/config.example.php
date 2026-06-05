<?php
/**
 * إعدادات Branch Agent
 * انسخ هذا الملف باسم config.php واملأ القيم المطلوبة
 */
return [
    // رابط API السيرفر الرئيسي (لا تغير المسار /api/fingerprint-agent/push)
    'server_url' => 'http://26.158.101.62:8080/NEXA/api/fingerprint-agent/push',

    // التوكن الخاص بهذا الفرع — انسخه من صفحة إعدادات الجهاز في البرنامج
    'api_token'  => 'fkjW5YjFWFkiRllTQs2p7ifebUGYcCAgWONOULvJc78FBb9RQB8QE8ldwG0fxXtZ',

    // عنوان IP جهاز البصمة على الشبكة المحلية للفرع
    'device_ip'  => '192.168.1.201',

    // البورت (الافتراضي لـ ZKTeco: 4370)
    'device_port' => 4370,
];
