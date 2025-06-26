const API_KEY = "YOUR_TMDB_API_KEY"; // Get from https://www.themoviedb.org/settings/api
const BASE_URL = "https://api.themoviedb.org/3";
const IMG_BASE_URL = "https://image.tmdb.org/t/p/w500";

let currentTab = "search";
let favorites = JSON.parse(localStorage.getItem("movieFavorites")) || [];
let genres = [];
let currentFilters = {
    genre: '',
    year: '',
    rating: 0,
    sort: 'popularity.desc'
};

// DOM Elements
const searchInput = document.getElementById("search-input");
const searchBtn = document.getElementById("search-btn");
const movieGrid = document.getElementById("movie-grid");
const loadingEl = document.getElementById("loading");
const tabButtons = document.querySelectorAll(".tab-btn");
const favCountEl = document.getElementById("fav-count");
const movieModal = new bootstrap.Modal(document.getElementById('movieModal'));

// Initialize
document.addEventListener("DOMContentLoaded", async () => {
    await loadGenres();
    await loadYearOptions();
    updateFavCount();
    loadPopularMovies();
    setupEventListeners();
    setupFilterListeners();
});

function setupEventListeners() {
    // Search button click
    searchBtn.addEventListener("click", () => {
        const query = searchInput.value.trim();
        if (query) searchMovies(query);
    });

    // Enter key in search
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            const query = searchInput.value.trim();
            if (query) searchMovies(query);
        }
    });

    // Tab switching
    tabButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            tabButtons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            currentTab = btn.dataset.tab;
            
            if (currentTab === "favorites") {
                displayFavorites();
            } else {
                if (searchInput.value.trim() === "") {
                    loadPopularMovies();
                } else {
                    searchMovies(searchInput.value.trim());
                }
            }
        });
    });
}

function setupFilterListeners() {
    document.getElementById('genre-filter').addEventListener('change', (e) => {
        currentFilters.genre = e.target.value;
        applyFilters();
    });
    
    document.getElementById('year-filter').addEventListener('change', (e) => {
        currentFilters.year = e.target.value;
        applyFilters();
    });
    
    document.getElementById('rating-filter').addEventListener('change', (e) => {
        currentFilters.rating = parseFloat(e.target.value);
        applyFilters();
    });
    
    document.getElementById('sort-by').addEventListener('change', (e) => {
        currentFilters.sort = e.target.value;
        applyFilters();
    });
}

async function loadGenres() {
    try {
        const response = await fetch(`${BASE_URL}/genre/movie/list?api_key=${API_KEY}`);
        const data = await response.json();
        genres = data.genres;
        
        const genreSelect = document.getElementById('genre-filter');
        genres.forEach(genre => {
            const option = document.createElement('option');
            option.value = genre.id;
            option.textContent = genre.name;
            genreSelect.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading genres:", error);
    }
}

function loadYearOptions() {
    const yearSelect = document.getElementById('year-filter');
    const currentYear = new Date().getFullYear();
    
    for (let year = currentYear; year >= 1970; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    }
}

function applyFilters() {
    if (currentTab === "favorites") {
        displayFavorites();
    } else {
        if (searchInput.value.trim() === "") {
            loadPopularMovies();
        } else {
            searchMovies(searchInput.value.trim());
        }
    }
}

// Load popular movies on startup
async function loadPopularMovies() {
    toggleLoading(true);
    try {
        let url = `${BASE_URL}/movie/popular?api_key=${API_KEY}`;
        
        // Add filters to URL
        url += `&sort_by=${currentFilters.sort}`;
        if (currentFilters.genre) url += `&with_genres=${currentFilters.genre}`;
        if (currentFilters.year) url += `&year=${currentFilters.year}`;
        if (currentFilters.rating) url += `&vote_average.gte=${currentFilters.rating}`;
        
        const response = await fetch(url);
        const data = await response.json();
        displayMovies(data.results);
    } catch (error) {
        console.error("Error fetching popular movies:", error);
    } finally {
        toggleLoading(false);
    }
}

// Search movies by query
async function searchMovies(query) {
    if (!query) return;
    
    toggleLoading(true);
    try {
        let url = `${BASE_URL}/search/movie?api_key=${API_KEY}&query=${query}`;
        
        // Add filters to URL
        url += `&sort_by=${currentFilters.sort}`;
        if (currentFilters.genre) url += `&with_genres=${currentFilters.genre}`;
        if (currentFilters.year) url += `&year=${currentFilters.year}`;
        if (currentFilters.rating) url += `&vote_average.gte=${currentFilters.rating}`;
        
        const response = await fetch(url);
        const data = await response.json();
        displayMovies(data.results);
    } catch (error) {
        console.error("Error searching movies:", error);
    } finally {
        toggleLoading(false);
    }
}

// Display movies in grid
function displayMovies(movies) {
    if (!movies || movies.length === 0) {
        movieGrid.innerHTML = "<p class='text-center'>No movies found. Try another search.</p>";
        return;
    }

    movieGrid.innerHTML = movies.map(movie => `
        <div class="movie-card animate-fade-in">
            <img src="${movie.poster_path ? IMG_BASE_URL + movie.poster_path : 'https://via.placeholder.com/300x450?text=No+Poster'}" 
                 alt="${movie.title}" 
                 class="movie-poster">
            <div class="movie-info">
                <h3 class="movie-title">${movie.title}</h3>
                <p class="movie-year">${movie.release_date ? movie.release_date.substring(0, 4) : 'N/A'}</p>
                <div class="movie-rating">
                    <i class="fas fa-star"></i>
                    <span>${movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A'}</span>
                </div>
            </div>
            <button class="favorite-btn ${isFavorite(movie.id) ? 'favorited' : ''}" 
                    data-movie-id="${movie.id}">
                <i class="fas fa-heart"></i>
            </button>
        </div>
    `).join("");

    // Add click event to movie cards
    document.querySelectorAll(".movie-card").forEach((card, index) => {
        card.addEventListener("click", () => showMovieDetails(movies[index]));
    });

    // Add click event to favorite buttons
    document.querySelectorAll(".favorite-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleFavorite(btn.dataset.movieId);
            btn.classList.toggle("favorited");
        });
    });
}

// Display favorite movies
function displayFavorites() {
    if (favorites.length === 0) {
        movieGrid.innerHTML = "<p class='text-center'>No favorites yet. Search for movies to add some!</p>";
        return;
    }

    // Fetch details for all favorites
    Promise.all(favorites.map(id => fetchMovieDetails(id)))
        .then(movies => {
            // Apply filters to favorites (except sort)
            let filteredMovies = movies.filter(movie => movie !== null);
            
            if (currentFilters.genre) {
                filteredMovies = filteredMovies.filter(movie => 
                    movie.genres.some(genre => genre.id.toString() === currentFilters.genre)
                );
            }
            
            if (currentFilters.year) {
                filteredMovies = filteredMovies.filter(movie => 
                    movie.release_date && movie.release_date.startsWith(currentFilters.year)
                );
            }
            
            if (currentFilters.rating) {
                filteredMovies = filteredMovies.filter(movie => 
                    movie.vote_average >= currentFilters.rating
                );
            }
            
            displayMovies(filteredMovies);
        })
        .catch(error => {
            console.error("Error fetching favorites:", error);
        });
}

// Fetch detailed movie info
async function fetchMovieDetails(movieId) {
    try {
        const response = await fetch(`${BASE_URL}/movie/${movieId}?api_key=${API_KEY}`);
        return await response.json();
    } catch (error) {
        console.error("Error fetching movie details:", error);
        return null;
    }
}

// Show movie details in modal
async function showMovieDetails(movie) {
    try {
        // Fetch additional details (cast, etc.)
        const [credits, details] = await Promise.all([
            fetch(`${BASE_URL}/movie/${movie.id}/credits?api_key=${API_KEY}`).then(res => res.json()),
            fetch(`${BASE_URL}/movie/${movie.id}?api_key=${API_KEY}`).then(res => res.json())
        ]);

        // Get top 5 cast members
        const cast = credits.cast.slice(0, 5).map(actor => actor.name).join(", ");

        // Format genres
        const genres = details.genres.map(genre => genre.name).join(", ");

        // Update modal content
        document.getElementById("movieModalTitle").textContent = movie.title;
        document.getElementById("movieModalBody").innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <img src="${movie.poster_path ? IMG_BASE_URL + movie.poster_path : 'https://via.placeholder.com/300x450?text=No+Poster'}" 
                         alt="${movie.title}" 
                         class="img-fluid rounded">
                </div>
                <div class="col-md-8">
                    <h3>${movie.title} <span class="text-muted">(${movie.release_date ? movie.release_date.substring(0, 4) : 'N/A'})</span></h3>
                    <div class="mb-3">
                        <span class="badge bg-primary me-2">${movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A'} <i class="fas fa-star"></i></span>
                        <span class="text-muted">${details.runtime ? `${details.runtime} mins` : 'N/A'}</span>
                    </div>
                    <p><strong>Genres:</strong> ${genres || 'N/A'}</p>
                    <p><strong>Cast:</strong> ${cast || 'N/A'}</p>
                    <h5 class="mt-4">Overview</h5>
                    <p>${movie.overview || 'No overview available.'}</p>
                    <button class="btn ${isFavorite(movie.id) ? 'btn-danger' : 'btn-outline-danger'} mt-3" 
                            id="modal-fav-btn" 
                            data-movie-id="${movie.id}">
                        <i class="fas fa-heart"></i> ${isFavorite(movie.id) ? 'Remove from Favorites' : 'Add to Favorites'}
                    </button>
                </div>
            </div>
        `;

        // Add event to modal favorite button
        document.getElementById("modal-fav-btn").addEventListener("click", (e) => {
            toggleFavorite(e.target.dataset.movieId);
            movieModal.hide();
            if (currentTab === "favorites") displayFavorites();
        });

        movieModal.show();
    } catch (error) {
        console.error("Error showing movie details:", error);
    }
}

// Favorite functions
function toggleFavorite(movieId) {
    if (isFavorite(movieId)) {
        favorites = favorites.filter(id => id != movieId);
    } else {
        favorites.push(movieId);
    }
    localStorage.setItem("movieFavorites", JSON.stringify(favorites));
    updateFavCount();
}

function isFavorite(movieId) {
    return favorites.includes(movieId.toString());
}

function updateFavCount() {
    favCountEl.textContent = favorites.length;
}

// Loading state
function toggleLoading(isLoading) {
    loadingEl.style.display = isLoading ? "flex" : "none";
    movieGrid.style.display = isLoading ? "none" : "grid";
}