// DOM Elements
const questionElement = document.getElementById('question');
const optionsElement = document.getElementById('options');
const scoreElement = document.getElementById('score');
const timeElement = document.getElementById('time');
const nextButton = document.getElementById('next-btn');
const progressBar = document.getElementById('progress-bar');
const themeToggle = document.getElementById('theme-toggle');
const showAdminBtn = document.getElementById('show-admin');
const adminPanel = document.getElementById('admin-panel');
const closeAdminBtn = document.getElementById('close-admin');
const addQuestionBtn = document.getElementById('add-question-btn');

// Quiz Variables
let questions = [];
let currentQuestionIndex = 0;
let score = 0;
let timeLeft = 30;
let timer;

// Initialize the quiz
async function initQuiz() {
    // Load default questions
    const defaultQuestions = [
        {
            question: "What is the capital of France?",
            options: ["Berlin", "Madrid", "Paris", "Rome"],
            correctAnswer: "Paris"
        },
        {
            question: "Which language runs in a web browser?",
            options: ["Java", "C", "Python", "JavaScript"],
            correctAnswer: "JavaScript"
        }
    ];
    
    // Load questions from localStorage if available
    const savedQuestions = localStorage.getItem('quizQuestions');
    questions = savedQuestions ? JSON.parse(savedQuestions) : defaultQuestions;
    
    startQuiz();
}

// Start the quiz
function startQuiz() {
    currentQuestionIndex = 0;
    score = 0;
    scoreElement.textContent = score;
    showQuestion();
    startTimer();
}

// Display current question
function showQuestion() {
    if (currentQuestionIndex >= questions.length) {
        endQuiz();
        return;
    }
    
    const currentQuestion = questions[currentQuestionIndex];
    questionElement.textContent = currentQuestion.question;
    optionsElement.innerHTML = '';
    
    currentQuestion.options.forEach(option => {
        const button = document.createElement('button');
        button.textContent = option;
        button.classList.add('option-btn');
        button.addEventListener('click', () => selectAnswer(option));
        optionsElement.appendChild(button);
    });
    
    updateProgressBar();
    nextButton.style.display = 'none';
}

// Handle answer selection
function selectAnswer(selectedOption) {
    const currentQuestion = questions[currentQuestionIndex];
    
    // Disable all options
    document.querySelectorAll('.option-btn').forEach(btn => {
        btn.disabled = true;
        if (btn.textContent === currentQuestion.correctAnswer) {
            btn.style.backgroundColor = '#28a745'; // Green for correct
        } else if (btn.textContent === selectedOption && selectedOption !== currentQuestion.correctAnswer) {
            btn.style.backgroundColor = '#dc3545'; // Red for wrong
        }
    });
    
    // Update score if correct
    if (selectedOption === currentQuestion.correctAnswer) {
        score++;
        scoreElement.textContent = score;
    }
    
    nextButton.style.display = 'block';
}

// Move to next question
function nextQuestion() {
    currentQuestionIndex++;
    if (currentQuestionIndex < questions.length) {
        resetOptions();
        showQuestion();
    } else {
        endQuiz();
    }
}

// Reset option buttons
function resetOptions() {
    document.querySelectorAll('.option-btn').forEach(btn => {
        btn.disabled = false;
        btn.style.backgroundColor = '';
    });
}

// Timer functionality
function startTimer() {
    clearInterval(timer);
    timeLeft = 30;
    timeElement.textContent = timeLeft;
    
    timer = setInterval(() => {
        timeLeft--;
        timeElement.textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            endQuiz();
        }
    }, 1000);
}

// Update progress bar
function updateProgressBar() {
    const progress = ((currentQuestionIndex + 1) / questions.length) * 100;
    progressBar.style.width = `${progress}%`;
}

// End quiz
function endQuiz() {
    clearInterval(timer);
    
    // Save high score
    const highScores = JSON.parse(localStorage.getItem('highScores')) || [];
    highScores.push({
        score: score,
        total: questions.length,
        date: new Date().toLocaleString()
    });
    localStorage.setItem('highScores', JSON.stringify(highScores));
    
    // Show results
    questionElement.innerHTML = `
        <h2>Quiz Completed!</h2>
        <p>Your score: <strong>${score}/${questions.length}</strong></p>
        <button id="restart-btn" class="btn">Restart Quiz</button>
    `;
    
    optionsElement.innerHTML = '';
    nextButton.style.display = 'none';
    
    document.getElementById('restart-btn').addEventListener('click', () => {
        location.reload();
    });
}

// Theme toggle
themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    themeToggle.textContent = document.body.classList.contains('dark-mode') 
        ? '☀️ Light Mode' 
        : '🌙 Dark Mode';
});

// Admin panel functionality
showAdminBtn.addEventListener('click', () => {
    adminPanel.style.display = 'block';
});

closeAdminBtn.addEventListener('click', () => {
    adminPanel.style.display = 'none';
});

addQuestionBtn.addEventListener('click', () => {
    const newQuestion = document.getElementById('new-question').value.trim();
    const correctAnswer = document.getElementById('correct-answer').value.trim();
    const option1 = document.getElementById('option1').value.trim();
    const option2 = document.getElementById('option2').value.trim();
    const option3 = document.getElementById('option3').value.trim();
    
    if (!newQuestion || !correctAnswer || !option1 || !option2 || !option3) {
        alert('Please fill all fields!');
        return;
    }
    
    const question = {
        question: newQuestion,
        correctAnswer: correctAnswer,
        options: [correctAnswer, option1, option2, option3].sort(() => Math.random() - 0.5)
    };
    
    questions.push(question);
    localStorage.setItem('quizQuestions', JSON.stringify(questions));
    
    // Clear form
    document.querySelectorAll('#admin-panel input, #admin-panel textarea').forEach(el => el.value = '');
    adminPanel.style.display = 'none';
    alert('Question added successfully!');
});

// Initialize the quiz when page loads
document.addEventListener('DOMContentLoaded', initQuiz);

// Event listeners
nextButton.addEventListener('click', nextQuestion);