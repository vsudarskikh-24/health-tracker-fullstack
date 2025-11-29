// ========================================
// ТРЕКЕР ЗДОРОВЬЯ - ОСНОВНОЙ ФУНКЦИОНАЛ
// ========================================

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
});

// ========================================
// ИНИЦИАЛИЗАЦИЯ
// ========================================
function initializePage() {
    const currentPage = getCurrentPage();
    
    // Общие инициализации
    setupNavigation();
    loadUserData();
    
    // Специфичные для страницы инициализации
    switch(currentPage) {
        case 'sleep':
            initializeSleepPage();
            break;
        case 'food':
            initializeFoodPage();
            break;
        case 'water':
            initializeWaterPage();
            break;
        case 'steps':
            initializeStepsPage();
            break;
        case 'statistics':
            initializeStatisticsPage();
            break;
        case 'goals':
            initializeGoalsPage();
            break;
        case 'dashboard':
            initializeDashboard();
            break;
    }
}

function getCurrentPage() {
    const path = window.location.pathname;
    const page = path.split('/').pop().replace('.html', '');
    return page || 'index';
}

// ========================================
// НАВИГАЦИЯ
// ========================================
function setupNavigation() {
    // Обработка кнопки "Назад"
    const backButtons = document.querySelectorAll('.back');
    backButtons.forEach(button => {
        button.addEventListener('click', () => {
            window.location.href = 'dashboard.html';
        });
    });
    
    // Обработка логотипа
    const logos = document.querySelectorAll('.logo');
    logos.forEach(logo => {
        logo.addEventListener('click', () => {
            window.location.href = 'dashboard.html';
        });
    });
}

// ========================================
// РАБОТА С ДАННЫМИ
// ========================================
function loadUserData() {
    // Загрузка данных из localStorage
    const userData = localStorage.getItem('healthTrackerUser');
    if (userData) {
        return JSON.parse(userData);
    }
    return null;
}

function saveUserData(data) {
    const existingData = loadUserData() || {};
    const updatedData = { ...existingData, ...data };
    localStorage.setItem('healthTrackerUser', JSON.stringify(updatedData));
}

function getTodayData() {
    const today = new Date().toISOString().split('T')[0];
    const dailyData = localStorage.getItem(`healthTracker_${today}`);
    if (dailyData) {
        return JSON.parse(dailyData);
    }
    return {
        sleep: null,
        water: 0,
        steps: 0,
        meals: []
    };
}

function saveTodayData(data) {
    const today = new Date().toISOString().split('T')[0];
    const existingData = getTodayData();
    const updatedData = { ...existingData, ...data };
    localStorage.setItem(`healthTracker_${today}`, JSON.stringify(updatedData));
}

// ========================================
// УЧЕТ СНА
// ========================================
function initializeSleepPage() {
    const sleepTimeInput = document.querySelector('input[type="time"]');
    const wakeTimeInput = document.querySelectorAll('input[type="time"]')[1];
    const saveButton = document.querySelector('.button');
    const stars = document.querySelectorAll('.star');
    
    let selectedRating = 0;
    
    // Загрузка существующих данных
    const todayData = getTodayData();
    if (todayData.sleep) {
        sleepTimeInput.value = todayData.sleep.bedTime;
        wakeTimeInput.value = todayData.sleep.wakeTime;
        selectedRating = todayData.sleep.quality;
        updateStars(selectedRating);
    }
    
    // Расчет продолжительности сна
    function calculateSleepDuration() {
        if (sleepTimeInput.value && wakeTimeInput.value) {
            const bedTime = new Date(`2000-01-01 ${sleepTimeInput.value}`);
            let wakeTime = new Date(`2000-01-01 ${wakeTimeInput.value}`);
            
            if (wakeTime < bedTime) {
                wakeTime = new Date(`2000-01-02 ${wakeTimeInput.value}`);
            }
            
            const diff = wakeTime - bedTime;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            
            const resultElement = document.querySelector('.result');
            if (resultElement) {
                resultElement.textContent = `${hours}.${Math.round(minutes / 6)} часов`;
            }
        }
    }
    
    sleepTimeInput.addEventListener('change', calculateSleepDuration);
    wakeTimeInput.addEventListener('change', calculateSleepDuration);
    
    // Оценка качества сна
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            selectedRating = index + 1;
            updateStars(selectedRating);
        });
    });
    
    function updateStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
    
    // Сохранение данных
    saveButton.addEventListener('click', () => {
        const sleepData = {
            sleep: {
                bedTime: sleepTimeInput.value,
                wakeTime: wakeTimeInput.value,
                quality: selectedRating,
                date: new Date().toISOString()
            }
        };
        
        saveTodayData(sleepData);
        showNotification('Данные о сне сохранены!');
        setTimeout(() => {
            window.location.href = 'dashboard.html';
        }, 1500);
    });
    
    calculateSleepDuration();
}

// ========================================
// УЧЕТ ПИТАНИЯ
// ========================================
function initializeFoodPage() {
    const tabs = document.querySelectorAll('.tab');
    const addButton = document.querySelector('.add-button');
    const saveButton = document.querySelector('.button');
    
    let currentMeal = 'breakfast';
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            if (tab.textContent.includes('Завтрак')) currentMeal = 'breakfast';
            if (tab.textContent.includes('Обед')) currentMeal = 'lunch';
            if (tab.textContent.includes('Ужин')) currentMeal = 'dinner';
            
            loadMealData(currentMeal);
        });
    });
    
    addButton.addEventListener('click', () => {
        const productName = prompt('Название продукта:');
        const productAmount = prompt('Количество:');
        
        if (productName && productAmount) {
            addFoodItem(productName, productAmount);
        }
    });
    
    saveButton.addEventListener('click', () => {
        showNotification('Данные о питании сохранены!');
        setTimeout(() => {
            window.location.href = 'dashboard.html';
        }, 1500);
    });
    
    function addFoodItem(name, amount) {
        const card = document.querySelector('.card');
        const newItem = document.createElement('div');
        newItem.className = 'meal-item';
        newItem.innerHTML = `
            <span>${name}</span>
            <span>${amount}</span>
        `;
        card.insertBefore(newItem, addButton);
    }
    
    function loadMealData(meal) {
        // Здесь можно загрузить сохраненные данные для конкретного приема пищи
    }
}

// ========================================
// УЧЕТ ВОДЫ
// ========================================
function initializeWaterPage() {
    const minusBtn = document.querySelector('.counter-btn:first-child');
    const plusBtn = document.querySelector('.counter-btn:last-child');
    const counterValue = document.querySelector('.counter-value');
    const addButton = document.querySelector('.button');
    const waterValue = document.querySelector('.water-value');
    const progressFill = document.querySelector('.progress-fill');
    
    let amount = 250;
    let totalWater = getTodayData().water || 0;
    const goal = 2500;
    
    updateDisplay();
    
    minusBtn.addEventListener('click', () => {
        if (amount > 50) {
            amount -= 50;
            counterValue.textContent = amount;
        }
    });
    
    plusBtn.addEventListener('click', () => {
        amount += 50;
        counterValue.textContent = amount;
    });
    
    addButton.addEventListener('click', () => {
        totalWater += amount;
        saveTodayData({ water: totalWater });
        updateDisplay();
        showNotification(`Добавлено ${amount} мл`);
        
        if (totalWater >= goal) {
            showNotification('🎉 Цель по воде достигнута!');
        }
    });
    
    function updateDisplay() {
        waterValue.textContent = `${totalWater} мл`;
        const progress = Math.min((totalWater / goal) * 100, 100);
        progressFill.style.width = `${progress}%`;
    }
}

// ========================================
// УЧЕТ ШАГОВ
// ========================================
function initializeStepsPage() {
    const stepsInput = document.querySelector('.input');
    const saveButton = document.querySelector('.button');
    const stepsValue = document.querySelector('.steps-value');
    const progressFill = document.querySelector('.progress-fill');
    
    const goal = 10000;
    let currentSteps = getTodayData().steps || 0;
    
    stepsInput.value = currentSteps;
    updateDisplay();
    
    saveButton.addEventListener('click', () => {
        currentSteps = parseInt(stepsInput.value) || 0;
        saveTodayData({ steps: currentSteps });
        updateDisplay();
        showNotification('Данные о шагах сохранены!');
        
        if (currentSteps >= goal) {
            showNotification('🎉 Цель по шагам достигнута!');
        }
        
        setTimeout(() => {
            window.location.href = 'dashboard.html';
        }, 1500);
    });
    
    function updateDisplay() {
        stepsValue.textContent = currentSteps.toLocaleString();
        const progress = Math.min((currentSteps / goal) * 100, 100);
        progressFill.style.width = `${progress}%`;
    }
}

// ========================================
// СТАТИСТИКА
// ========================================
function initializeStatisticsPage() {
    const tabs = document.querySelectorAll('.tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            const period = tab.textContent.toLowerCase();
            loadStatistics(period);
        });
    });
    
    loadStatistics('неделя');
}

function loadStatistics(period) {
    // Здесь будет логика загрузки статистики за выбранный период
    console.log(`Загрузка статистики за ${period}`);
}

// ========================================
// ЦЕЛИ
// ========================================
function initializeGoalsPage() {
    const editButton = document.querySelector('.button');
    const addButton = document.querySelector('.button-outline');
    
    editButton.addEventListener('click', () => {
        showNotification('Режим редактирования целей');
    });
    
    addButton.addEventListener('click', () => {
        showNotification('Добавление новой цели');
    });
    
    updateGoalsProgress();
}

function updateGoalsProgress() {
    const todayData = getTodayData();
    const goals = {
        sleep: 8,
        water: 2500,
        steps: 10000,
        meals: 4
    };
    
    // Обновление прогресса целей на основе текущих данных
    const sleepProgress = todayData.sleep ? 
        Math.min((calculateSleepHours(todayData.sleep) / goals.sleep) * 100, 100) : 0;
    const waterProgress = Math.min((todayData.water / goals.water) * 100, 100);
    const stepsProgress = Math.min((todayData.steps / goals.steps) * 100, 100);
    const mealsProgress = Math.min((todayData.meals.length / goals.meals) * 100, 100);
}

function calculateSleepHours(sleepData) {
    const bedTime = new Date(`2000-01-01 ${sleepData.bedTime}`);
    let wakeTime = new Date(`2000-01-01 ${sleepData.wakeTime}`);
    
    if (wakeTime < bedTime) {
        wakeTime = new Date(`2000-01-02 ${sleepData.wakeTime}`);
    }
    
    const diff = wakeTime - bedTime;
    return diff / (1000 * 60 * 60);
}

// ========================================
// ДАШБОРД
// ========================================
function initializeDashboard() {
    updateDashboardStats();
    
    const addDataButton = document.querySelector('.button');
    if (addDataButton) {
        addDataButton.addEventListener('click', () => {
            showQuickAddMenu();
        });
    }
}

function updateDashboardStats() {
    const todayData = getTodayData();
    const progressBars = document.querySelectorAll('.progress-fill');
    const statValues = document.querySelectorAll('.stat-value');
    
    // Обновление отображения статистики на дашборде
    if (todayData.sleep && statValues[0]) {
        const hours = calculateSleepHours(todayData.sleep);
        statValues[0].textContent = `${hours.toFixed(1)}ч`;
        if (progressBars[0]) {
            progressBars[0].style.width = `${Math.min((hours / 8) * 100, 100)}%`;
        }
    }
    
    if (statValues[1]) {
        statValues[1].textContent = `${(todayData.water / 1000).toFixed(1)}л`;
        if (progressBars[1]) {
            progressBars[1].style.width = `${Math.min((todayData.water / 2500) * 100, 100)}%`;
        }
    }
    
    if (statValues[2]) {
        statValues[2].textContent = todayData.steps.toLocaleString();
        if (progressBars[2]) {
            progressBars[2].style.width = `${Math.min((todayData.steps / 10000) * 100, 100)}%`;
        }
    }
    
    if (statValues[3]) {
        statValues[3].textContent = todayData.meals.length;
        if (progressBars[3]) {
            progressBars[3].style.width = `${Math.min((todayData.meals.length / 4) * 100, 100)}%`;
        }
    }
}

function showQuickAddMenu() {
    const options = ['Сон', 'Питание', 'Вода', 'Шаги'];
    const choice = prompt(`Выберите что добавить:\n1. ${options[0]}\n2. ${options[1]}\n3. ${options[2]}\n4. ${options[3]}`);
    
    switch(choice) {
        case '1':
            window.location.href = 'sleep.html';
            break;
        case '2':
            window.location.href = 'food.html';
            break;
        case '3':
            window.location.href = 'water.html';
            break;
        case '4':
            window.location.href = 'steps.html';
            break;
    }
}

// ========================================
// УВЕДОМЛЕНИЯ
// ========================================
function showNotification(message) {
    // Создание всплывающего уведомления
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4caf50;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Добавление CSS анимаций
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);