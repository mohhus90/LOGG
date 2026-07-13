/**
 * Arabic -> English employee-name transliteration.
 * Requires window.EMP_DICT_GET_URL, window.EMP_DICT_SAVE_URL, window.EMP_CSRF_TOKEN
 * to be set before this file is loaded.
 */
(function ($) {
    const arToEnNames = {
        'محمد':'Mohamed','محمود':'Mahmoud','أحمد':'Ahmed','علي':'Ali','حسن':'Hassan',
        'حسين':'Hussein','عمر':'Omar','عمرو':'Amr','إبراهيم':'Ibrahim','إسماعيل':'Ismail',
        'يوسف':'Youssef','يحيى':'Yahya','ياسر':'Yasser','ياسين':'Yassin',
        'عبدالله':'Abdullah','عبدالرحمن':'Abdelrahman','عبدالرحيم':'Abdelrahim',
        'عبدالعزيز':'Abdelaziz','عبدالحميد':'Abdelhamid','عبدالفتاح':'Abdelfattah',
        'عبدالمنعم':'Abdelmoneam','عبدالسلام':'Abdelsalam',
        'مصطفى':'Mustafa','خالد':'Khaled','طارق':'Tarek','سامي':'Sami',
        'وليد':'Walid','رامي':'Rami','هاني':'Hany','كريم':'Karim','عماد':'Emad',
        'أسامة':'Osama','شريف':'Sherif','مروان':'Marwan','جمال':'Gamal',
        'فريد':'Farid','بسام':'Bassam','ناصر':'Nasser','سعد':'Saad',
        'فتحي':'Fathy','صلاح':'Salah','ماهر':'Maher','نادر':'Nader',
        'عادل':'Adel','سيد':'Sayed','منصور':'Mansour','فيصل':'Faisal',
        'زياد':'Ziad','باسم':'Bassem','أيمن':'Ayman','هشام':'Hisham',
        'مدحت':'Medhat','نبيل':'Nabil','عصام':'Essam','داود':'Dawood',
        'سليمان':'Soliman','جابر':'Gaber','رضا':'Reda','صابر':'Saber',
        'فاروق':'Farouk','عزت':'Ezzat','أنور':'Anwar','منير':'Monir',
        'تامر':'Tamer','بهاء':'Bahaa','إياد':'Eyad','ثروت':'Tharwat',
        'حامد':'Hamed','حمزة':'Hamza','زكريا':'Zakaria','رفعت':'Refaat',
        'ربيع':'Rabie','سعيد':'Saeed','سلامة':'Salama','عطية':'Attia',
        'قدري':'Qadry','مجدي':'Magdy','منتصر':'Montaser','نصر':'Nasr',
        'هادي':'Hady','وجدي':'Wagdy','يسري':'Yosry','أمين':'Amin',
        'أنس':'Anas','بدر':'Badr','حازم':'Hazem','حاتم':'Hatem',
        'خيري':'Khairy','دياب':'Diab','لطفي':'Lotfy','توفيق':'Tawfik',
        'حمدي':'Hamdy','صبري':'Sobhy','مختار':'Mokhtar','رشاد':'Rashad',
        'رشيد':'Rashid','سمير':'Samir','شوقي':'Shawky','علاء':'Alaa',
        'فهمي':'Fahmy','قاسم':'Kassem','كمال':'Kamal','ممدوح':'Mamdouh',
        'نجيب':'Naguib','هيثم':'Haytham','وائل':'Wael','حلمي':'Helmy',
        'خليل':'Khalil','درويش':'Darwish','زهير':'Zohair','سالم':'Salem',
        'شحاتة':'Shahata','طه':'Taha','عبير':'Abeer','فايز':'Fayez',
        'ماجد':'Maged','نصير':'Nassir','هدى':'Hoda','وسيم':'Wassim',
        'يونس':'Younis','أبوبكر':'Abubakr','بكر':'Bakr','جاد':'Gad',
        'خضر':'Khedr','راغب':'Ragheb','زكي':'Zaki','صفوت':'Safwat',
        'ضياء':'Diaa','طلعت':'Talaat','عفيفي':'Afify','فخري':'Fakhry',
        'كرم':'Karam','لبيب':'Labib','مأمون':'Mamoon','نزار':'Nizar',
        'هاشم':'Hashem','وادي':'Wady',
        'فاطمة':'Fatma','عائشة':'Aisha','مريم':'Mariam','سارة':'Sara',
        'نور':'Nour','هبة':'Heba','رنا':'Rana','آية':'Aya','دينا':'Dina',
        'سمر':'Samar','إيمان':'Eman','منى':'Mona','نهاد':'Nehad',
        'هالة':'Hala','ريم':'Reem','لبنى':'Lobna','إنجي':'Engy',
        'شيماء':'Shimaa','وفاء':'Wafaa','ولاء':'Walaa','سلمى':'Salma',
        'غادة':'Ghada','لمياء':'Lamia','نيفين':'Neveen','ياسمين':'Yasmine',
        'أسماء':'Asmaa','بسمة':'Basma','حنان':'Hanan','خديجة':'Khadiga',
        'رشا':'Rasha','زينب':'Zainab','سناء':'Sanaa','صفاء':'Safaa',
        'عفاف':'Afaf','مي':'Mai','نادية':'Nadia','أميرة':'Amira',
        'جيهان':'Gehan','رقية':'Rokaya','شادية':'Shadia','عزة':'Azza',
        'فريدة':'Farida','كريمة':'Karima','لطيفة':'Latifa','نادين':'Nadine',
        'هناء':'Hanaa','إلهام':'Elham','أمل':'Amal','تهاني':'Tahany',
        'حياة':'Hayat','درية':'Doria','عبلة':'Abla','نجلاء':'Naglaa',
        'هويدا':'Howayda','وسام':'Wesam','ميرنا':'Mirna','نيرة':'Nayra',
        'حنين':'Haneen','رهف':'Rahaf','سهير':'Sohair','شروق':'Shorouk',
        'صباح':'Sabah','ضحى':'Doha','فايزة':'Fayza','مها':'Maha',
        'نوران':'Nouran','وجدان':'Wagdan','يمنى':'Yomna',
        'رمضان':'Ramadan','غانم':'Ghanem','نجم':'Nagm','هيكل':'Heikal',
        'مرسي':'Morsy','عوض':'Awad','زيدان':'Zedan','بدوي':'Badawy',
        'حجازي':'Hegazy','شرف':'Sharaf','متولي':'Metwaly','دسوقي':'Desouky',
        'رفاعي':'Refaay','هلال':'Helal','وهبة':'Wahba','ملاك':'Malak',
        'نعمة':'Neama','ديب':'Deeb','قطب':'Qotb','منيم':'Moneim',
        'منجد':'Mongad','حلبي':'Halaby','زناتي':'Zenaty','شبراوي':'Shebrawy',
        'غزالي':'Ghazaly','فقي':'Fiky','قرشي':'Qorashi','كيلاني':'Kilany',
    };

    $.get(window.EMP_DICT_GET_URL, function (data) {
        $.each(data, function (i, item) {
            arToEnNames[item.ar_name] = item.en_name;
        });
    });

    function normalizeAr(w) {
        return w.replace(/[ً-ٰٟ]/g, '')
                .replace(/[أإآ]/g, 'ا')
                .replace(/ة$/, 'ة');
    }

    function arToEn(text) {
        return text.trim().split(/\s+/).map(function (word) {
            const clean = normalizeAr(word);
            if (arToEnNames[word])  return arToEnNames[word];
            if (arToEnNames[clean]) return arToEnNames[clean];
            for (const [ar, en] of Object.entries(arToEnNames)) {
                if (normalizeAr(ar) === clean) return en;
            }
            return '[' + word + ']';
        }).join(' ');
    }

    function isArabic(text) { return /[؀-ۿ]/.test(text); }

    let lastUnknownWords = [];

    function renderDictionaryPanel() {
        $('#dict-panel').remove();
        if (lastUnknownWords.length === 0) return;

        let rows = '';
        lastUnknownWords.forEach(function (arWord) {
            rows += '<div class="input-group input-group-sm mb-1">' +
                '<div class="input-group-prepend">' +
                '<span class="input-group-text" style="min-width:110px;font-weight:bold;">' + arWord + '</span>' +
                '</div>' +
                '<span class="input-group-text">=</span>' +
                '<input type="text" class="form-control dict-en-input" dir="ltr" ' +
                'placeholder="English translation" data-ar="' + arWord + '">' +
                '</div>';
        });

        const panel = '<div id="dict-panel" class="mt-2 p-2 border border-warning rounded bg-light">' +
            '<small class="text-warning font-weight-bold d-block mb-1">' +
            '<i class="fas fa-exclamation-triangle ml-1"></i>' +
            'Words not in dictionary — add their translation to save for next time' +
            '</small>' +
            rows +
            '<button type="button" id="btn-save-dict" class="btn btn-warning btn-sm mt-1">' +
            '<i class="fas fa-save ml-1"></i> Save to Dictionary' +
            '</button>' +
            '</div>';

        $('#employee_name_E').closest('.col-md-4').append(panel);

        $('#btn-save-dict').on('click', function () {
            const entries = [];
            $('.dict-en-input').each(function () {
                const ar = $(this).data('ar');
                const en = $(this).val().trim();
                if (en) entries.push({ ar: ar, en: en });
            });
            if (entries.length === 0) {
                alert('Please enter the English translation first');
                return;
            }
            $.ajax({
                url: window.EMP_DICT_SAVE_URL,
                method: 'POST',
                data: { _token: window.EMP_CSRF_TOKEN, entries: entries },
                success: function () {
                    entries.forEach(function (e) { arToEnNames[e.ar] = e.en; });
                    const arVal = $('#employee_name_A').val();
                    if (arVal) $('#employee_name_E').val(arToEn(arVal));
                    lastUnknownWords = [];
                    $('#dict-panel').remove();
                    $('<div class="alert alert-success alert-dismissible py-1 mt-1" id="dict-saved-msg">' +
                      '<strong>Saved!</strong> Words added to dictionary.' +
                      '<button type="button" class="close py-1" data-dismiss="alert">&times;</button>' +
                      '</div>').insertAfter('#employee_name_E');
                    setTimeout(function () { $('#dict-saved-msg').fadeOut(500, function () { $(this).remove(); }); }, 3000);
                },
                error: function () { alert('An error occurred, please try again'); }
            });
        });
    }

    $(document).ready(function () {
        $('#employee_name_A').on('input', function () {
            const val = $(this).val();
            if (!isArabic(val)) return;

            const result = arToEn(val);
            $('#employee_name_E').val(result);

            lastUnknownWords = [];
            const arWords = val.trim().split(/\s+/);
            const enWords = result.trim().split(/\s+/);
            arWords.forEach(function (w, i) {
                if (enWords[i] && enWords[i].startsWith('[')) {
                    lastUnknownWords.push(w);
                }
            });

            renderDictionaryPanel();
        });
    });
})(jQuery);
