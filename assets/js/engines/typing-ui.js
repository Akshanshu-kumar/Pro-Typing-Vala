// assets/js/engines/typing-ui.js

document.addEventListener('DOMContentLoaded', () => {
    // UI Elements
    const startBtn = document.getElementById('startTestBtn');
    const resetBtn = document.getElementById('resetBtn');
    const submitBtn = document.getElementById('submitBtn');
    const languageSelect = document.getElementById('languageSelect');
    const modeSelect = document.getElementById('modeSelect');
    const timeSelect = document.getElementById('timeSelect');
    const keyboardToggle = document.getElementById('keyboardToggle');
    
    const typingArea = document.getElementById('typing-area');
    const hiddenInput = document.getElementById('hiddenInput');
    const timerDisplay = document.getElementById('timer');
    const wpmDisplay = document.getElementById('wpm');
    const accuracyDisplay = document.getElementById('accuracy');
    const errorsDisplay = document.getElementById('errors');
    
    let engine = null;
    let currentText = "";

    // Event Listeners
    startBtn.addEventListener('click', initTest);
    resetBtn.addEventListener('click', () => {
        if(engine) engine.reset();
        initTest();
    });
    
    submitBtn.addEventListener('click', () => {
        if(engine) engine.finish();
    });

    modeSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value.startsWith('exam_')) {
            timeSelect.value = selected.dataset.duration * 60;
            timeSelect.disabled = true;
        } else {
            timeSelect.disabled = false;
        }
    });
    
    keyboardToggle.addEventListener('change', function() {
        const kbContainer = document.getElementById('virtualKeyboardContainer');
        if(this.checked) {
            kbContainer.classList.remove('d-none');
            // Render keyboard based on language (To be implemented in layouts.js)
        } else {
            kbContainer.classList.add('d-none');
        }
    });

    // Typing Interaction
    typingArea.addEventListener('click', () => {
        if(engine && !engine.isFinished) hiddenInput.focus();
    });

    hiddenInput.addEventListener('input', (e) => {
        if (!engine) return;
        
        if (e.inputType === 'deleteContentBackward') {
             const res = engine.processInput(null, 'deleteContentBackward');
             if (res.action === 'backspace') {
                 // Remove style from char
                 const chars = typingArea.querySelectorAll('.char');
                 if (chars[res.index]) {
                     chars[res.index].className = 'char'; // Reset classes
                     chars[res.index].classList.add('current');
                     if (chars[res.index+1]) chars[res.index+1].classList.remove('current');
                 }
             }
        } else if (e.data) {
             const res = engine.processInput(e.data, 'insertText');
             if (res.action === 'input') {
                 const chars = typingArea.querySelectorAll('.char');
                 const charEl = chars[res.index];
                 
                 charEl.classList.remove('current');
                 if (res.isCorrect) {
                     charEl.classList.add('correct');
                 } else {
                     charEl.classList.add('incorrect');
                 }
                 
                 if (chars[res.index + 1]) {
                     chars[res.index + 1].classList.add('current');
                     // Scroll
                     if(chars[res.index+1].offsetTop > typingArea.scrollTop + typingArea.clientHeight - 40) {
                         typingArea.scrollTop += 35;
                     }
                 }
             }
        }
        hiddenInput.value = "";
    });

    function initTest() {
        // Fetch text based on language and difficulty
        startBtn.disabled = true;
        typingArea.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div></div>';
        
        const lang = languageSelect.value;
        const difficulty = 'medium'; // Could be added to UI

        fetch(`api/get_paragraph.php?language=${lang}&difficulty=${difficulty}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setupEngine(data.data.content);
                } else {
                    alert('Could not load text');
                    startBtn.disabled = false;
                }
            });
    }

    function setupEngine(text) {
        currentText = text;
        renderText(text);
        
        const modeOption = modeSelect.options[modeSelect.selectedIndex];
        const duration = parseInt(timeSelect.value);
        const allowBackspace = modeOption.dataset.backspace === undefined ? true : (modeOption.dataset.backspace === '1');
        
        engine = new TypingEngine({
            text: text,
            language: languageSelect.value,
            mode: modeSelect.value,
            duration: duration,
            allowBackspace: allowBackspace,
            onTick: updateStats,
            onFinish: finishTest,
            onInput: updateLiveStats
        });

        // UI Updates
        resetBtn.disabled = false;
        submitBtn.disabled = false;
        hiddenInput.focus();
        timerDisplay.innerText = formatTime(duration);
    }

    function renderText(text) {
        typingArea.innerHTML = '';
        text.split('').forEach(char => {
            const span = document.createElement('span');
            span.innerText = char;
            span.className = 'char';
            typingArea.appendChild(span);
        });
        typingArea.firstChild.classList.add('current');
    }

    function updateStats(stats) {
        timerDisplay.innerText = formatTime(stats.timeLeft);
        wpmDisplay.innerText = stats.wpm;
    }
    
    function updateLiveStats(result) {
        // Optional: Update errors count immediately
    }

    function finishTest(stats) {
        hiddenInput.blur();
        submitBtn.disabled = true;
        
        // Save Result
        const payload = {
            wpm: stats.wpm,
            accuracy: stats.accuracy,
            mistakes: stats.mistakes,
            time_taken: parseInt(timeSelect.value) - (engine ? engine.timeLeft : 0),
            cpm: Math.round(stats.totalChars / ((parseInt(timeSelect.value) - (engine ? engine.timeLeft : 0))/60) || 0)
        };
        
        fetch('api/save_result.php', {
            method: 'POST',
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                window.location.href = `result.php?id=${data.result_id}`;
            }
        });
    }
    
    function formatTime(s) {
        const m = Math.floor(s/60);
        const sec = s % 60;
        return `${m}:${sec<10?'0':''}${sec}`;
    }
});
