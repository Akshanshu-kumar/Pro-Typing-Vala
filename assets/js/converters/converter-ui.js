// assets/js/converters/converter-ui.js

document.addEventListener('DOMContentLoaded', () => {
    const sourceText = document.getElementById('sourceText');
    const destText = document.getElementById('destText');
    const convertBtn = document.getElementById('convertBtn');
    const clearBtn = document.getElementById('clearBtn');
    const copyBtn = document.getElementById('copyBtn');
    const swapBtn = document.getElementById('swapBtn');
    const conversionType = document.getElementById('conversionType');
    const sourceCount = document.getElementById('sourceCount');
    const destCount = document.getElementById('destCount');

    // Update Counts
    sourceText.addEventListener('input', () => {
        sourceCount.innerText = sourceText.value.length;
    });

    // Convert Action
    convertBtn.addEventListener('click', () => {
        const text = sourceText.value;
        const type = conversionType.value;
        let result = "";

        if (type === 'unicode_to_krutidev') {
            result = convertUnicodeToKrutiDev(text);
        } else if (type === 'krutidev_to_unicode') {
            result = convertKrutiDevToUnicode(text);
        } else {
            result = "Conversion not implemented yet for this type.";
        }

        destText.value = result;
        destCount.innerText = result.length;
    });

    // Clear Action
    clearBtn.addEventListener('click', () => {
        sourceText.value = "";
        destText.value = "";
        sourceCount.innerText = "0";
        destCount.innerText = "0";
    });

    // Copy Action
    copyBtn.addEventListener('click', () => {
        destText.select();
        document.execCommand('copy');
        alert('Copied to clipboard!');
    });

    // Swap Action
    swapBtn.addEventListener('click', () => {
        // Swap text
        const tempText = sourceText.value;
        sourceText.value = destText.value;
        destText.value = tempText;
        
        // Swap counts
        sourceCount.innerText = sourceText.value.length;
        destCount.innerText = destText.value.length;

        // Swap conversion type logic (simplified)
        if(conversionType.value === 'unicode_to_krutidev') {
            conversionType.value = 'krutidev_to_unicode';
        } else if (conversionType.value === 'krutidev_to_unicode') {
            conversionType.value = 'unicode_to_krutidev';
        }
    });
});
