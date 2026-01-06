// assets/js/converters/converter-map.js

/**
 * Full Kruti Dev 010 to Unicode Mapping
 * Based on Industry Standard Layout
 */

const KRUTI_DEV_MAP = {
    // 1. Basic Alphabets (Vyanjan)
    "d": "क", "D": "क्",
    "k": "ा",
    "j": "ि", "f": "ि",
    "g": "ु",
    "h": "प",
    "l": "स",
    ";": "य",
    "a": "म",
    "s": "े",
    "w": "ै",
    "q": "ु",
    "e": "ी",
    "r": "र",
    "t": "त",
    "y": "य",
    "u": "ु",
    "i": "ि",
    "o": "ो",
    "p": "प",
    "[": "[",
    "]": "]",

    // 2. Full Vyanjan Set
    "c": "ब", "C": "भ",
    "x": "ग", "X": "घ",
    "v": "न",
    "b": "च",
    "n": "ल",
    "m": "ह",
    ",": "ष",
    ".": "।",
    "/": "्र",

    // 3. Swar (Vowels)
    "A": "अ",
    "B": "आ",
    "E": "ई",
    "I": "इ",
    "O": "ओ",
    "U": "उ",
    "R": "ऋ",

    // 4. Matra Mapping (Overrides/Additions)
    "S": "ै",
    "H": "ू",
    "O": "ौ",
    "M": "ं",
    "~": "ँ",
    ":": "ः",

    // 5. Half Letters
    "्": "्",
    "Z": "्र",
    "z": "्",

    // 6. Numbers
    "0": "०", "1": "१", "2": "२", "3": "३", "4": "४", 
    "5": "५", "6": "६", "7": "७", "8": "८", "9": "९",

    // Additional Common Mappings (Extended)
    "'": "ठ", "\"": "ठ्",
    "L": "स्",
    "K": "GYA_PLACEHOLDER", // Requires Special Handling usually 'Ks'
    "?": "घ्", 
};

// Reverse Map for Unicode to Kruti Dev (Generated from above)
const UNICODE_TO_KRUTI_MAP = {};
for (let key in KRUTI_DEV_MAP) {
    let value = KRUTI_DEV_MAP[key];
    // Simple reverse mapping (might need manual adjustments for collisions)
    if (!UNICODE_TO_KRUTI_MAP[value]) {
        UNICODE_TO_KRUTI_MAP[value] = key;
    }
}

/**
 * Convert Kruti Dev (Legacy ASCII) to Unicode (Mangal)
 */
function convertKrutiDevToUnicode(text) {
    let converted = "";
    
    // Process character by character (Simple Substitution)
    // Note: A full converter requires complex reordering for 'i' matra (chhoti-ee)
    // 'f' (chhoti-ee) comes BEFORE the consonant in Kruti Dev, but AFTER in Unicode logic (conceptually)
    // But physically in Unicode string, it is Consonant + Matra.
    // In Kruti (Visual): Matra + Consonant.
    // So "fd" (ki) -> "क" + "ि" -> "कि"
    
    // We need a loop that handles this reordering.
    
    let chars = text.split('');
    for (let i = 0; i < chars.length; i++) {
        let char = chars[i];
        
        // Handle 'f' (Chhoti Ee) - It comes BEFORE the consonant in Kruti Dev
        // But in Unicode, it goes AFTER.
        // Pattern in Kruti: f + Consonant -> Consonant + Matra
        if (char === 'f') {
            // Look ahead for the consonant
            let nextChar = chars[i+1];
            if (nextChar && KRUTI_DEV_MAP[nextChar]) {
                converted += KRUTI_DEV_MAP[nextChar]; // Consonant first
                converted += "ि"; // Then Matra
                i++; // Skip next char as we used it
                continue;
            }
        }
        
        // Normal Mapping
        if (KRUTI_DEV_MAP[char]) {
            converted += KRUTI_DEV_MAP[char];
        } else {
            converted += char;
        }
    }
    
    return converted;
}

/**
 * Convert Unicode (Mangal) to Kruti Dev (Legacy ASCII)
 */
function convertUnicodeToKrutiDev(text) {
    let converted = "";
    let chars = text.split('');
    
    for (let i = 0; i < chars.length; i++) {
        let char = chars[i];
        
        // Handle 'ि' (Chhoti Ee) - It comes AFTER consonant in Unicode
        // But needs to be 'f' BEFORE consonant in Kruti Dev.
        // This is tricky because we need to know "what was the previous consonant?"
        // Simpler approach: If we see a Consonant, check if next is 'ि'.
        
        // Check for Consonant + 'ि'
        if (chars[i+1] === 'ि') {
            converted += "f"; // 'f' comes first in Kruti
            if (UNICODE_TO_KRUTI_MAP[char]) {
                converted += UNICODE_TO_KRUTI_MAP[char];
            } else {
                converted += char;
            }
            i++; // Skip the matra
            continue;
        }

        if (UNICODE_TO_KRUTI_MAP[char]) {
            converted += UNICODE_TO_KRUTI_MAP[char];
        } else {
            converted += char;
        }
    }
    
    return converted;
}
