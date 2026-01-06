// assets/js/typing-test.js

document.addEventListener('DOMContentLoaded', () => {
    const typingArea = document.getElementById('typing-area');
    const hiddenInput = document.getElementById('hiddenInput'); 
    const wpmDisplay = document.getElementById('wpm');
    const accuracyDisplay = document.getElementById('accuracy');
    const errorsDisplay = document.getElementById('errors');
    const timerDisplay = document.getElementById('timer');
    const startTestBtn = document.getElementById('startTestBtn'); // legacy
    const startExamBtn = document.getElementById('startExamBtn');
    const startPracticeBtn = document.getElementById('startPracticeBtn');
    const resetBtn = document.getElementById('resetBtn');
    const submitBtn = document.getElementById('submitBtn');
    const focusOverlay = document.getElementById('focusOverlay');
    
    // Settings
    const languageSelect = document.getElementById('languageSelect');
    const modeSelect = document.getElementById('modeSelect');
    const timeSelect = document.getElementById('timeSelect');
    const keyboardToggle = document.getElementById('keyboardToggle');
    const nameInput = document.getElementById('testerName');
    const defaultParagraphCheck = document.getElementById('defaultParagraphCheck');
    const customParagraphCheck = document.getElementById('customParagraphCheck');
    const defaultParagraphType = document.getElementById('defaultParagraphType');
    const customText = document.getElementById('customText');
    const uploadTextBtn = document.getElementById('uploadTextBtn');
    const wordLimitEnable = document.getElementById('wordLimitEnable');
    const wordLimitInput = document.getElementById('wordLimitInput');
    const backspaceEnable = document.getElementById('backspaceEnable');
    const backspaceDisable = document.getElementById('backspaceDisable');
    const highlightEnable = document.getElementById('highlightEnable');
    const highlightDisable = document.getElementById('highlightDisable');
    const virtualKeyboardContainer = document.getElementById('virtualKeyboardContainer');

    // Server-provided settings from separate settings page
    const TEST_SETTINGS = (typeof window !== 'undefined' && window.TEST_SETTINGS) ? window.TEST_SETTINGS : null;
    const getSetting = (key, defVal) => {
        if (!TEST_SETTINGS) return defVal;
        return TEST_SETTINGS[key] !== undefined ? TEST_SETTINGS[key] : defVal;
    };

    let timeLeft = 60;
    let totalTime = 60;
    let timer = null;
    let isRunning = false;
    let charIndex = 0;
    let mistakes = 0;
    let fullMistakes = 0;
    let halfMistakes = 0;
    let isFinished = false;
    let currentText = "";
    let allowBackspace = true;
    let enableHighlightScroll = true;
    let selectedMode = 'practice';
    
    // Modal elements
    const resultModalEl = document.getElementById('resultModal');
    let resultModal = null;
    if (resultModalEl && typeof bootstrap !== 'undefined') {
        resultModal = new bootstrap.Modal(resultModalEl);
    }
    const saveResultBtn = document.getElementById('saveResultBtn');
    const retakeBtn = document.getElementById('retakeBtn');

    // Initialize
    if(startTestBtn) {
        startTestBtn.addEventListener('click', startTest);
    }
    if(startPracticeBtn) {
        startPracticeBtn.addEventListener('click', () => {
             // Logic for practice mode if distinct, otherwise same as startTest
             startTest();
        });
    }
    if(startExamBtn) {
        startExamBtn.addEventListener('click', () => {
             // Logic for exam mode if distinct
             startTest();
        });
    }

    if(saveResultBtn) {
        saveResultBtn.addEventListener('click', saveResult);
    }
    if(retakeBtn) {
        retakeBtn.addEventListener('click', () => {
            if(resultModal) resultModal.hide();
            resetStats();
            startTest();
        });
    }
    
    if(resetBtn) {
        resetBtn.addEventListener('click', () => {
            if(confirm("Are you sure you want to reset the test?")) {
                startTest();
            }
        });
    }

    if(submitBtn) {
        submitBtn.addEventListener('click', () => {
            if(confirm("Are you sure you want to submit the test early?")) {
                finishTest();
            }
        });
    }

    // Font size controls
    const fontIncBtn = document.getElementById('fontIncBtn');
    const fontDecBtn = document.getElementById('fontDecBtn');
    let currentFontSize = 24; // default px
    try {
        const cs = window.getComputedStyle(typingArea);
        const parsed = parseFloat(cs.fontSize);
        if (!isNaN(parsed)) currentFontSize = parsed;
    } catch(_) {}
    const applyFontSize = () => {
        typingArea.style.fontSize = currentFontSize + 'px';
        if (hiddenInput) hiddenInput.style.fontSize = currentFontSize + 'px';
    };
    if (fontIncBtn) {
        fontIncBtn.addEventListener('click', () => {
            currentFontSize = Math.min(currentFontSize + 2, 48);
            applyFontSize();
        });
    }
    if (fontDecBtn) {
        fontDecBtn.addEventListener('click', () => {
            currentFontSize = Math.max(currentFontSize - 2, 12);
            applyFontSize();
        });
    }
    applyFontSize();

    if(keyboardToggle && virtualKeyboardContainer) {
        keyboardToggle.addEventListener('change', () => {
            if(keyboardToggle.checked) {
                virtualKeyboardContainer.classList.remove('d-none');
            } else {
                virtualKeyboardContainer.classList.add('d-none');
            }
        });
    }
    
    // Focus Overlay Logic
    if(focusOverlay && hiddenInput) {
        // When overlay is clicked, focus input
        focusOverlay.addEventListener('click', () => {
            if(isRunning && !isFinished) {
                hiddenInput.focus();
            }
        });

        // When input loses focus, show overlay
        hiddenInput.addEventListener('blur', () => {
            if(isRunning && !isFinished) {
                focusOverlay.classList.remove('d-none');
                focusOverlay.classList.add('d-flex');
            }
        });

        // When input gets focus, hide overlay
        hiddenInput.addEventListener('focus', () => {
            focusOverlay.classList.remove('d-flex');
            focusOverlay.classList.add('d-none');
        });
    }

    function startTest() {
        const lang = languageSelect ? languageSelect.value : getSetting('language', 'english');
        const duration = timeSelect ? parseInt(timeSelect.value) : parseInt(getSetting('time', 60));
        // Backspace
        if (backspaceEnable || backspaceDisable) {
            allowBackspace = backspaceEnable ? backspaceEnable.checked : true;
            if (backspaceDisable && backspaceDisable.checked) allowBackspace = false;
        } else {
            allowBackspace = (getSetting('backspace', 'enable') === 'enable');
        }
        // Highlight & Auto Scroll
        if (highlightEnable || highlightDisable) {
            enableHighlightScroll = highlightEnable ? highlightEnable.checked : true;
            if (highlightDisable && highlightDisable.checked) enableHighlightScroll = false;
        } else {
            enableHighlightScroll = (getSetting('highlight', 'enable') === 'enable');
        }

        // Update UI State
        if (startTestBtn) startTestBtn.disabled = true;
        resetBtn.disabled = false;
        submitBtn.disabled = false;
        isFinished = false;

        // Set Font Class
        typingArea.className = "border rounded p-4 mb-3 position-relative"; // Reset
        if(lang === 'hindi_krutidev') {
            typingArea.classList.add('font-krutidev');
        } else if (lang === 'hindi_inscript' || lang === 'marathi') {
            typingArea.classList.add('font-mangal');
        }

        // Fetch Paragraph
        const customProvided = (customText && customText.value && customText.value.trim().length > 0) ? customText.value.trim() : getSetting('custom_text', '').trim();
        const useCustomCheckbox = customParagraphCheck ? customParagraphCheck.checked : false;
        const useCustom = (customProvided.length > 0 && !!TEST_SETTINGS) || (useCustomCheckbox && customProvided.length > 0);
        if (useCustom) {
            loadParagraph(customProvided);
        } else {
            fetchParagraph(lang);
        }
        
        // Setup Timer
        timeLeft = duration;
        totalTime = duration;
        timerDisplay.innerText = formatTime(timeLeft);
        
        // Reset Stats
        resetStats();
        
        // Focus
        hiddenInput.value = "";
        hiddenInput.focus();
        isRunning = true;
        
        // Timer does NOT start here. It starts on first input.
        clearInterval(timer);
        timer = null;
    }

    function fetchParagraph(lang) {
        typingArea.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;
        
        let url = `api/get_paragraph.php?language=${lang}`;
        const fromDb = defaultParagraphType ? (defaultParagraphType.value === 'database') : (getSetting('paragraph_type', 'random') === 'database');
        const limitEnabled = wordLimitEnable ? wordLimitEnable.checked : !!getSetting('word_limit_enabled', false);
        const count = limitEnabled ? parseInt((wordLimitInput ? (wordLimitInput.value || '200') : getSetting('word_limit', 200))) : 200;
        if (lang === 'english' && !fromDb) { url += `&random=1&count=${count}`; }
        else { url += `&count=${count}`; }
            
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadParagraph(data.data.content);
                } else {
                    typingArea.innerHTML = "Error loading text. Please try another language.";
                }
            })
            .catch(err => {
                console.error(err);
                typingArea.innerHTML = "Error loading text.";
            });
    }

    function loadParagraph(text) {
        currentText = text;
        typingArea.innerHTML = "";
        text.split("").forEach(char => {
            let span = document.createElement("span");
            span.innerText = char;
            span.classList.add('char');
            typingArea.appendChild(span);
        });
        if(typingArea.querySelectorAll('.char').length > 0) {
            typingArea.querySelectorAll('.char')[0].classList.add('current');
        }
    }

    // Typing Logic
    hiddenInput.addEventListener('input', (e) => {
        if (!isRunning || isFinished) return;

        // Start Timer on first keystroke if not running
        if (!timer) {
            timer = setInterval(updateTimer, 1000);
        }

        const charSpans = typingArea.querySelectorAll('.char');
        const typedVal = hiddenInput.value;
        const typedChar = typedVal[typedVal.length - 1]; // Last typed char
        
        if (typedVal.length < charIndex) {
            // Backspace handling
            if (!allowBackspace) return;
            if (charIndex > 0) {
                charSpans[charIndex].classList.remove('current');
                charIndex--;
                charSpans[charIndex].classList.remove('correct', 'incorrect');
                if (enableHighlightScroll) { charSpans[charIndex].classList.add('current'); }
            }
            return;
        }

        // If we reach end of current text, append more (time-based test should continue)
        if (charIndex >= currentText.length) {
            if (languageSelect.value === 'english') {
                appendMoreText();
            } else {
                return;
            }
        }

        let targetChar = currentText[charIndex];
        
        if (typedChar === targetChar) {
            charSpans[charIndex].classList.add('correct');
        } else {
            mistakes++;
            errorsDisplay.innerText = mistakes;
            charSpans[charIndex].classList.add('incorrect');
        }
        
        charSpans[charIndex].classList.remove('current');
        charIndex++;
        
        if (charIndex < charSpans.length) {
            if (enableHighlightScroll) {
                charSpans[charIndex].classList.add('current');
                const currentSpan = charSpans[charIndex];
                const areaRect = typingArea.getBoundingClientRect();
                const spanRect = currentSpan.getBoundingClientRect();
                if (spanRect.bottom > areaRect.bottom - 20) {
                    currentSpan.scrollIntoView({ behavior: "smooth", block: "center" });
                }
            }
        } else {
            // At the end boundary, try to append more for English random words
            if (languageSelect.value === 'english') {
                appendMoreText();
            } else {
                finishTest();
            }
        }
        
        calculateStats();
    });
    hiddenInput.addEventListener('keydown', (e) => {
        if (!allowBackspace && e.key === 'Backspace') {
            e.preventDefault();
        }
    });
    
    // Prevent default behavior for some keys if needed
    typingArea.addEventListener('click', () => {
        if(isRunning && !isFinished) hiddenInput.focus();
    });

    function updateTimer() {
        if (timeLeft > 0) {
            timeLeft--;
            timerDisplay.innerText = formatTime(timeLeft);
            calculateStats();
        } else {
            finishTest();
        }
    }
    
    function finishTest() {
        clearInterval(timer);
        timer = null;
        isRunning = false;
        isFinished = true;
        hiddenInput.blur();
        
        // Update UI State
        if (startTestBtn) startTestBtn.disabled = false;
        if (startPracticeBtn) startPracticeBtn.disabled = false;
        if (startExamBtn) startExamBtn.disabled = false;
        
        resetBtn.disabled = true;
        submitBtn.disabled = true;
        
        if(focusOverlay) {
            focusOverlay.classList.remove('d-flex');
            focusOverlay.classList.add('d-none');
        }
        
        // Auto save for logged-in users; otherwise stash and preview
        try {
            if (typeof IS_LOGGED_IN !== 'undefined' && IS_LOGGED_IN) {
                saveResult();
            } else {
                stashAndPreview();
            }
        } catch(_) {
            stashAndPreview();
        }
    }

    function stashAndPreview() {
        const wpm = wpmDisplay.innerText;
        const acc = accuracyDisplay.innerText.replace('%','');
        const timeTaken = totalTime - timeLeft;
        const cpm = Math.round((charIndex) / (timeTaken / 60));
        const timeMinutes = timeTaken / 60;
        const grossWpm = Math.round((charIndex / 5) / (timeMinutes || 1));
        const errorRateWpm = Math.round((mistakes) / (timeMinutes || 1));
        
        const payload = {
            wpm: wpm,
            cpm: isFinite(cpm) ? cpm : 0,
            accuracy: acc,
            mistakes: mistakes,
            time_taken: timeTaken,
            gross_wpm: isFinite(grossWpm) ? grossWpm : 0,
            error_rate_wpm: isFinite(errorRateWpm) ? errorRateWpm : 0,
            language: languageSelect ? languageSelect.value : getSetting('language', 'english'),
            tester_name: nameInput ? nameInput.value : ''
        };

        fetch('api/stash_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                window.location.href = 'result.php?preview=1';
            } else {
                alert('Error processing result');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error processing result');
        });
    }

    // Modal no longer used, kept for reference or removal
    function showResultModal() {
        // ... (Disabled in favor of full page redirect)
    }
    
    function calculateStats() {
        let timeElapsed = totalTime - timeLeft;
        if(timeElapsed === 0) timeElapsed = 1; // Prevent div by zero
        
        let wpm = Math.round(((charIndex - mistakes) / 5) / (timeElapsed / 60));
        wpm = wpm < 0 || !isFinite(wpm) ? 0 : wpm;
        wpmDisplay.innerText = wpm;
        
        let accuracy = Math.round(((charIndex - mistakes) / charIndex) * 100);
        accuracy = accuracy < 0 || !isFinite(accuracy) ? 100 : accuracy;
        accuracyDisplay.innerText = accuracy + "%";
    }
    
    function saveResult() {
        const wpm = wpmDisplay.innerText;
        const acc = accuracyDisplay.innerText.replace('%','');
        const timeTaken = totalTime - timeLeft;
        const cpm = Math.round((charIndex) / (timeTaken / 60));
        const timeMinutes = timeTaken / 60;
        const grossWpm = Math.round((charIndex / 5) / (timeMinutes || 1));
        const errorRateWpm = Math.round((mistakes) / (timeMinutes || 1));
        
        fetch('api/save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                wpm: wpm,
                cpm: isFinite(cpm) ? cpm : 0,
                accuracy: acc,
                mistakes: mistakes,
                time_taken: timeTaken,
                gross_wpm: isFinite(grossWpm) ? grossWpm : 0,
                error_rate_wpm: isFinite(errorRateWpm) ? errorRateWpm : 0,
                language: languageSelect ? languageSelect.value : getSetting('language', 'english'),
                tester_name: nameInput ? nameInput.value : getSetting('tester_name', '')
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                window.location.href = `result.php?id=${data.result_id}`;
            } else {
                // If not logged in, stash result and send to register
                if (data.message && data.message.toLowerCase().includes('user not logged in')) {
                    fetch('api/stash_result.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            wpm: wpm,
                            cpm: isFinite(cpm) ? cpm : 0,
                            accuracy: acc,
                            mistakes: mistakes,
                            full_mistakes: fullMistakes,
                            half_mistakes: halfMistakes,
                            typed_content: hiddenInput.value,
                            original_content: currentText,
                            time_taken: timeTaken,
                            gross_wpm: isFinite(grossWpm) ? grossWpm : 0,
                            error_rate_wpm: isFinite(errorRateWpm) ? errorRateWpm : 0,
                            language: languageSelect.value,
                            tester_name: nameInput ? nameInput.value : ''
                        })
                    })
                    .then(r => r.json())
                    .then(() => {
                        window.location.href = 'login.php';
                    })
                    .catch(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    alert(data.message || 'Error saving result. Please try again.');
                }
            }
        });
    }

    function resetStats() {
        charIndex = 0;
        mistakes = 0;
        isFinished = false;
        wpmDisplay.innerText = "0";
        accuracyDisplay.innerText = "100%";
        errorsDisplay.innerText = "0";
        hiddenInput.value = "";
    }

    function formatTime(s) {
        return (s - (s %= 60)) / 60 + (9 < s ? ':' : ':0') + s;
    }

    function appendMoreText() {
        const lang = languageSelect ? languageSelect.value : getSetting('language', 'english');
        let url = `api/get_paragraph.php?language=${lang}`;
        if (lang === 'english') {
            url += `&random=1&count=200`;
        }
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data && data.data.content) {
                    const moreText = data.data.content;
                    currentText += moreText;
                    moreText.split("").forEach(char => {
                        let span = document.createElement("span");
                        span.innerText = char;
                        span.classList.add('char');
                        typingArea.appendChild(span);
                    });
                    const charSpans = typingArea.querySelectorAll('.char');
                    if (charIndex < charSpans.length) {
                        charSpans[charIndex].classList.add('current');
                    }
                }
            })
            .catch(err => console.error(err));
    }
    
    // Auto-start when settings were provided from separate settings page
    if (typeof TEST_SETTINGS !== 'undefined' && TEST_SETTINGS && Object.keys(TEST_SETTINGS).length > 0) {
        startTest();
    }
});
