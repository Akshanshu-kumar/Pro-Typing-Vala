// assets/js/engines/typing-engine-core.js

class TypingEngine {
    constructor(config) {
        this.text = config.text;
        this.language = config.language;
        this.mode = config.mode || 'practice';
        this.onInput = config.onInput;
        this.currentIndex = 0;
        this.startTime = null;
        this.mistakes = 0;
        this.isFinished = false;
        
        // Bind methods
        this.processInput = this.processInput.bind(this);
    }

    start() {
        this.startTime = new Date();
    }

    processInput(inputVal, inputType) {
        if(this.isFinished) return;
        
        if(!this.startTime) this.start();

        // Current Target Character
        const targetChar = this.text[this.currentIndex];
        
        // Input Handling
        // For Tutor, we usually expect 1-to-1 matching
        // But for Hindi Inscript/Kruti, it might be complex.
        
        // Simple approach: Check the last character typed
        const typedChar = inputVal[inputVal.length - 1];
        
        // Compare
        let isCorrect = (typedChar === targetChar);
        
        // Special handling for Kruti Dev if we are in Tutor mode with Unicode text?
        // If the Tutor displays "क" (Unicode) and language is 'hindi_krutidev',
        // the user is expected to type 'd' (ASCII).
        // But the input field will receive 'd'.
        // So we need to map the Target Char (Unicode) to Expected Input (ASCII) before comparing.
        
        if (this.language === 'hindi_krutidev' && typeof UNICODE_TO_KRUTI_MAP !== 'undefined') {
            const expectedKey = UNICODE_TO_KRUTI_MAP[targetChar] || targetChar;
            isCorrect = (typedChar === expectedKey);
        }

        if (isCorrect) {
            this.onInput({
                action: 'input',
                isCorrect: true,
                index: this.currentIndex,
                char: targetChar
            });
            this.currentIndex++;
        } else {
            this.mistakes++;
            this.onInput({
                action: 'input',
                isCorrect: false,
                index: this.currentIndex,
                char: targetChar
            });
        }

        if (this.currentIndex >= this.text.length) {
            this.isFinished = true;
            this.onInput({
                action: 'finish',
                stats: this.getStats()
            });
        }
    }

    getStats() {
        const now = new Date();
        const timeTaken = (now - this.startTime) / 1000; // seconds
        const wpm = Math.round(((this.currentIndex / 5) / (timeTaken / 60)));
        const accuracy = Math.round(((this.currentIndex - this.mistakes) / this.currentIndex) * 100);
        
        return {
            wpm: wpm || 0,
            accuracy: accuracy || 0,
            mistakes: this.mistakes,
            time: timeTaken
        };
    }
}
