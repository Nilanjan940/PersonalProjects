const API_KEY = "YOUR_OPENWEATHER_API_KEY"; // Replace with your key
const RECENT_SEARCHES_KEY = "weatherRecentSearches";
let recentSearches = JSON.parse(localStorage.getItem(RECENT_SEARCHES_KEY)) || [];

// DOM Elements
const cityInput = document.getElementById("city-input");
const searchBtn = document.getElementById("search-btn");
const locationBtn = document.getElementById("location-btn");
const recentSearchesEl = document.getElementById("recent-searches");
const loadingEl = document.getElementById("loading");
const weatherCardEl = document.getElementById("weather-card");

// Initialize
document.addEventListener("DOMContentLoaded", () => {
    updateRecentSearchesUI();
    // Try to get user's location on load
    getLocationWeather();
});

// Search by city
searchBtn.addEventListener("click", () => {
    const city = cityInput.value.trim();
    if (city) {
        fetchWeather(city);
        addToRecentSearches(city);
    }
});

// Search by geolocation
locationBtn.addEventListener("click", getLocationWeather);

// Get weather by geolocation
function getLocationWeather() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                fetchWeatherByCoords(latitude, longitude);
            },
            (error) => {
                console.error("Geolocation error:", error);
                alert("Could not get your location. Please allow location access or search manually.");
            }
        );
    } else {
        alert("Geolocation is not supported by your browser.");
    }
}

// Fetch weather by coordinates
async function fetchWeatherByCoords(lat, lon) {
    try {
        toggleLoading(true);
        const response = await fetch(
            `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${API_KEY}&units=metric`
        );
        const data = await response.json();
        displayWeather(data);
        fetchForecast(data.name);
        addToRecentSearches(data.name);
    } catch (error) {
        console.error("Error fetching weather by location:", error);
    } finally {
        toggleLoading(false);
    }
}

// Fetch weather by city name
async function fetchWeather(city) {
    try {
        toggleLoading(true);
        const response = await fetch(
            `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${API_KEY}&units=metric`
        );
        const data = await response.json();
        
        if (data.cod === "404") {
            alert("City not found!");
            return;
        }

        displayWeather(data);
        fetchForecast(city);
    } catch (error) {
        console.error("Error fetching weather:", error);
    } finally {
        toggleLoading(false);
    }
}

// Fetch 5-day forecast
async function fetchForecast(city) {
    try {
        const response = await fetch(
            `https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${API_KEY}&units=metric`
        );
        const data = await response.json();
        displayForecast(data.list);
    } catch (error) {
        console.error("Error fetching forecast:", error);
    }
}

// Display current weather
function displayWeather(data) {
    document.getElementById("city-name").textContent = data.name;
    document.getElementById("temperature").textContent = `${Math.round(data.main.temp)}°C`;
    document.getElementById("weather-description").textContent = 
        data.weather[0].description.charAt(0).toUpperCase() + data.weather[0].description.slice(1);
    document.getElementById("humidity").textContent = `${data.main.humidity}%`;
    document.getElementById("wind").textContent = `${data.wind.speed} km/h`;
    
    // Set weather icon
    const icon = getWeatherIcon(data.weather[0].id, data.weather[0].icon);
    document.getElementById("weather-icon").innerHTML = `<i class="wi ${icon}"></i>`;
}

// Display 5-day forecast
function displayForecast(forecastData) {
    const forecastElement = document.getElementById("forecast");
    forecastElement.innerHTML = "";

    for (let i = 0; i < forecastData.length; i += 8) {
        const day = forecastData[i];
        const date = new Date(day.dt * 1000).toLocaleDateString("en", { weekday: "short" });
        const temp = Math.round(day.main.temp);
        const icon = getWeatherIcon(day.weather[0].id, day.weather[0].icon);

        forecastElement.innerHTML += `
            <div class="forecast-day">
                <p><strong>${date}</strong></p>
                <p><i class="wi ${icon}"></i></p>
                <p>${temp}°C</p>
            </div>
        `;
    }
}

// Get Weather Icon Class
function getWeatherIcon(weatherId, iconCode) {
    // Day/Night detection (OpenWeatherMap uses 'd' for day, 'n' for night)
    const isDay = iconCode.includes('d');
    
    if (weatherId >= 200 && weatherId < 300) {
        return isDay ? "wi wi-day-thunderstorm" : "wi wi-night-alt-thunderstorm";
    } else if (weatherId >= 300 && weatherId < 400) {
        return "wi wi-sprinkle";
    } else if (weatherId >= 500 && weatherId < 600) {
        return isDay ? "wi wi-day-rain" : "wi wi-night-alt-rain";
    } else if (weatherId >= 600 && weatherId < 700) {
        return isDay ? "wi wi-day-snow" : "wi wi-night-alt-snow";
    } else if (weatherId >= 700 && weatherId < 800) {
        return "wi wi-fog";
    } else if (weatherId === 800) {
        return isDay ? "wi wi-day-sunny" : "wi wi-night-clear";
    } else if (weatherId > 800) {
        return isDay ? "wi wi-day-cloudy" : "wi wi-night-alt-cloudy";
    } else {
        return "wi wi-cloud";
    }
}

// Recent Searches Functions
function addToRecentSearches(city) {
    if (!recentSearches.includes(city)) {
        recentSearches.unshift(city);
        if (recentSearches.length > 5) recentSearches.pop();
        localStorage.setItem(RECENT_SEARCHES_KEY, JSON.stringify(recentSearches));
        updateRecentSearchesUI();
    }
}

function updateRecentSearchesUI() {
    recentSearchesEl.innerHTML = "<small>Recent searches: </small>";
    recentSearches.forEach(city => {
        const span = document.createElement("span");
        span.className = "recent-search-item";
        span.textContent = city;
        span.addEventListener("click", () => {
            cityInput.value = city;
            fetchWeather(city);
        });
        recentSearchesEl.appendChild(span);
    });
}

// Loading State
function toggleLoading(isLoading) {
    loadingEl.style.display = isLoading ? "block" : "none";
    weatherCardEl.style.display = isLoading ? "none" : "block";
}