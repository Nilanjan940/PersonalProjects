const names = document.querySelector(".names")
const email = document.querySelector(".email")
const joined = document.querySelector(".joined")
const navBar = document.querySelector("nav")
const navToggle = document.querySelector(".navToggle")
const navLinks = document.querySelectorAll(".navList")
const darkToggle = document.querySelector(".darkToggle")
const body = document.querySelector("body")


navToggle.addEventListener('click',()=>{
    navBar.classList.toggle('close')
})

navLinks.forEach(function (element){
    element.addEventListener('click',function (){
        navLinks.forEach((e)=>{
            e.classList.remove('active')
            this.classList.add('active')
        })
    })
})


darkToggle.addEventListener('click',()=>{
    body.classList.toggle('dark')
})


const fetchedData = fetch("./data.json")
                    .then((data)=>{
                        return data.json()
                    })
                    .then(response=>{
                        console.log(response);
                        const items = response.item
                        let Name = ""
                        let Email = ""
                        let Joined = ""
                        
                        items.forEach((element)=>{
                            Name += `<span class="data-list">${element.name}</span>`
                            Email += `<span class="data-list">${element.email}</span>`
                            Joined += `<span class="data-list">${element.joined}</span>`
                        })
                        names.innerHTML += Name 
                        email.innerHTML += Email 
                        joined.innerHTML += Joined 
                    })


                    document.addEventListener('DOMContentLoaded', function () {
                        const categoryFilter = document.getElementById('categoryFilter');
                    
                        // Fetch categories and populate the combobox
                        // You need to implement the fetchCategories function to retrieve categories from your database
                        fetchCategories().then(categories => {
                            categories.forEach(category => {
                                const option = document.createElement('option');
                                option.value = category;
                                option.textContent = category;
                                categoryFilter.appendChild(option);
                            });
                        });
                    
                        // Add event listener to filter the table when a category is selected
                        categoryFilter.addEventListener('change', function () {
                            const selectedCategory = this.value;
                    
                            // Fetch and display products based on the selected category
                            // You need to implement the fetchProductsByCategory function
                            fetchProductsByCategory(selectedCategory);
                        });
                    });
                    
                    function fetchCategories() {
                        // Implement this function to fetch categories from your database
                        // You can use AJAX, fetch API, or any other method to make a server request
                        // Return a promise that resolves with an array of category names
                    }
                    
                    function fetchProductsByCategory(category) {
                        // Implement this function to fetch and display products based on the selected category
                        // You can use AJAX, fetch API, or any other method to make a server request
                        // Update the table content with the filtered data
                    }
                    