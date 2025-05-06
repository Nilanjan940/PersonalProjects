let currentMode = 'basic';
let currentBase = 'dec';
let displayValue = '0';
let historyValue = '';

const display = document.getElementById('display');
const history = document.getElementById('history');
const modeButtons = document.querySelectorAll('.mode-btn');
const keypads = document.querySelectorAll('.keypad');

// Initialize calculator
function init() {
    updateDisplay();
    setupModeSwitcher();
}

// Update display
function updateDisplay() {
    display.value = displayValue;
    history.textContent = historyValue;
}

// Setup mode switcher
function setupModeSwitcher() {
    modeButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            modeButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');
            // Set current mode
            currentMode = button.dataset.mode;
            // Show the appropriate keypad
            showKeypad(currentMode);
        });
    });
}

// Show the appropriate keypad based on mode
function showKeypad(mode) {
    keypads.forEach(keypad => {
        if (keypad.classList.contains(`${mode}-mode`)) {
            keypad.classList.remove('hidden');
        } else {
            keypad.classList.add('hidden');
        }
    });
    
    // Reset display when switching modes
    if (mode !== 'basic') {
        clearDisplay();
    }
}

// Append input to display
function appendToDisplay(input) {
    // Handle special cases
    if (input === '+/-') {
        if (displayValue.charAt(0) === '-') {
            displayValue = displayValue.substring(1);
        } else {
            displayValue = '-' + displayValue;
        }
    } 
    // Handle programmer mode inputs based on current base
    else if (currentMode === 'programmer') {
        if (currentBase === 'bin' && !['0', '1'].includes(input)) return;
        if (currentBase === 'oct' && !['0', '1', '2', '3', '4', '5', '6', '7'].includes(input)) return;
        if (currentBase === 'dec' && !['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'].includes(input)) return;
        if (currentBase === 'hex' && !['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'a', 'b', 'c', 'd', 'e', 'f'].includes(input)) return;
        
        if (displayValue === '0') {
            displayValue = input;
        } else {
            displayValue += input;
        }
    }
    // Handle normal cases
    else {
        if (displayValue === '0' && input !== '.') {
            displayValue = input;
        } else {
            displayValue += input;
        }
    }
    
    updateDisplay();
}

// Clear display
function clearDisplay() {
    displayValue = '0';
    historyValue = '';
    updateDisplay();
}

// Backspace
function backspace() {
    if (displayValue.length === 1) {
        displayValue = '0';
    } else {
        displayValue = displayValue.slice(0, -1);
    }
    updateDisplay();
}

// Calculate result
function calculate() {
    try {
        // Save current expression to history
        historyValue = displayValue;
        
        // Replace special symbols for evaluation
        let expression = displayValue
            .replace(/×/g, '*')
            .replace(/÷/g, '/')
            .replace(/\^/g, '**')
            .replace(/√\(/g, 'Math.sqrt(')
            .replace(/π/g, 'Math.PI')
            .replace(/e/g, 'Math.E')
            .replace(/log\(/g, 'Math.log10(')
            .replace(/ln\(/g, 'Math.log(');
        
        // Evaluate the expression
        displayValue = eval(expression).toString();
        
        // For programmer mode, convert to current base
        if (currentMode === 'programmer') {
            const num = parseInt(displayValue);
            switch (currentBase) {
                case 'bin':
                    displayValue = num.toString(2);
                    break;
                case 'oct':
                    displayValue = num.toString(8);
                    break;
                case 'hex':
                    displayValue = num.toString(16).toUpperCase();
                    break;
                // dec is already in correct format
            }
        }
        
        updateDisplay();
    } catch (error) {
        displayValue = 'Error';
        updateDisplay();
        setTimeout(clearDisplay, 1500);
    }
}

// Calculate scientific functions
function calculateFunction(func) {
    try {
        if (displayValue === '0') {
            displayValue = func + ')';
        } else {
            displayValue = func + displayValue + ')';
        }
        calculate();
    } catch (error) {
        displayValue = 'Error';
        updateDisplay();
        setTimeout(clearDisplay, 1500);
    }
}

// Factorial function
function factorial() {
    try {
        let num = parseInt(displayValue);
        if (num < 0) {
            displayValue = 'Error';
            updateDisplay();
            setTimeout(clearDisplay, 1500);
            return;
        }
        
        let result = 1;
        for (let i = 2; i <= num; i++) {
            result *= i;
        }
        
        displayValue = result.toString();
        updateDisplay();
    } catch (error) {
        displayValue = 'Error';
        updateDisplay();
        setTimeout(clearDisplay, 1500);
    }
}

// Change base in programmer mode
function changeBase(base) {
    if (currentMode !== 'programmer') return;
    
    currentBase = base;
    
    try {
        // Convert current display value to decimal first
        let decimalValue;
        if (displayValue === '0') {
            decimalValue = 0;
        } else {
            switch (currentBase) {
                case 'bin':
                    decimalValue = parseInt(displayValue, 2);
                    break;
                case 'oct':
                    decimalValue = parseInt(displayValue, 8);
                    break;
                case 'hex':
                    decimalValue = parseInt(displayValue, 16);
                    break;
                default: // dec
                    decimalValue = parseInt(displayValue);
            }
        }
        
        // Convert to new base
        switch (base) {
            case 'bin':
                displayValue = decimalValue.toString(2);
                break;
            case 'oct':
                displayValue = decimalValue.toString(8);
                break;
            case 'hex':
                displayValue = decimalValue.toString(16).toUpperCase();
                break;
            default: // dec
                displayValue = decimalValue.toString();
        }
        
        updateDisplay();
    } catch (error) {
        displayValue = 'Error';
        updateDisplay();
        setTimeout(clearDisplay, 1500);
    }
}

// Keyboard support
document.addEventListener('keydown', (e) => {
    if (e.key >= '0' && e.key <= '9') {
        appendToDisplay(e.key);
    } else if (e.key === '.') {
        appendToDisplay('.');
    } else if (e.key === '+' || e.key === '-' || e.key === '*' || e.key === '/') {
        appendToDisplay(e.key);
    } else if (e.key === 'Enter' || e.key === '=') {
        calculate();
    } else if (e.key === 'Escape') {
        clearDisplay();
    } else if (e.key === 'Backspace') {
        backspace();
    } else if (e.key === '(' || e.key === ')') {
        appendToDisplay(e.key);
    } else if (e.key === '%') {
        appendToDisplay('%');
    }
});

// Initialize calculator
init();