document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.getElementById('media-url');
    const downloadBtn = document.getElementById('download-btn');
    const downloadNowBtn = document.getElementById('download-now-btn');
    const previewArea = document.querySelector('.preview-area');
    const mediaThumbnail = document.getElementById('media-thumbnail');
    const mediaTitle = document.getElementById('media-title');
    const qualitySelect = document.getElementById('quality-select');
    const loading = document.querySelector('.loading');
    const platformIcons = document.querySelectorAll('.platform');

    // Platform icons click event
    platformIcons.forEach(platform => {
        platform.addEventListener('click', function() {
            const platformName = this.getAttribute('data-platform');
            let placeholder = '';
            
            switch(platformName) {
                case 'youtube':
                    placeholder = 'https://www.youtube.com/watch?v=...';
                    break;
                case 'instagram':
                    placeholder = 'https://www.instagram.com/p/...';
                    break;
                case 'facebook':
                    placeholder = 'https://www.facebook.com/.../videos/...';
                    break;
                case 'twitter':
                    placeholder = 'https://twitter.com/.../status/...';
                    break;
                case 'whatsapp':
                    placeholder = 'WhatsApp status video/image URL';
                    break;
                case 'tiktok':
                    placeholder = 'https://www.tiktok.com/@.../video/...';
                    break;
            }
            
            urlInput.placeholder = `Paste ${platformName.charAt(0).toUpperCase() + platformName.slice(1)} URL here (e.g. ${placeholder})`;
            urlInput.focus();
        });
    });

    // Download button click event
    downloadBtn.addEventListener('click', function() {
        const mediaUrl = urlInput.value.trim();
        
        if (!mediaUrl) {
            alert('Please enter a valid URL');
            return;
        }
        
        // Show loading spinner
        loading.classList.remove('hidden');
        
        // Simulate API call (in a real app, this would be an actual API call)
        setTimeout(() => {
            fetchMediaInfo(mediaUrl);
        }, 1500);
    });

    // Download now button click event
    downloadNowBtn.addEventListener('click', function() {
        const mediaUrl = urlInput.value.trim();
        const quality = qualitySelect.value;
        
        if (!quality) {
            alert('Please select a quality option');
            return;
        }
        
        // In a real app, this would send the quality selection to the server
        initiateDownload(mediaUrl, quality);
    });

    // Function to fetch media info (simulated)
    function fetchMediaInfo(url) {
        // Hide loading spinner
        loading.classList.add('hidden');
        
        // Check if URL is from a supported platform
        if (!isSupportedPlatform(url)) {
            alert('This platform is not supported or the URL is invalid');
            return;
        }
        
        // Simulate getting media info
        const platform = detectPlatform(url);
        const title = getSimulatedTitle(platform);
        const thumbnailUrl = getSimulatedThumbnail(platform);
        const qualities = getSimulatedQualities(platform);
        
        // Update UI with media info
        mediaTitle.textContent = title;
        mediaThumbnail.src = thumbnailUrl;
        
        // Populate quality options
        qualitySelect.innerHTML = '<option value="">Select Quality</option>';
        qualities.forEach(quality => {
            const option = document.createElement('option');
            option.value = quality.value;
            option.textContent = quality.label;
            qualitySelect.appendChild(option);
        });
        
        // Show preview area and download now button
        previewArea.classList.remove('hidden');
        downloadNowBtn.classList.remove('hidden');
    }

    // Function to initiate download
    function initiateDownload(url, quality) {
        // In a real app, this would redirect to a server-side download handler
        // For this example, we'll simulate it
        
        // Create a form dynamically
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'download.php';
        
        // Add URL input
        const urlInput = document.createElement('input');
        urlInput.type = 'hidden';
        urlInput.name = 'url';
        urlInput.value = url;
        form.appendChild(urlInput);
        
        // Add quality input
        const qualityInput = document.createElement('input');
        qualityInput.type = 'hidden';
        qualityInput.name = 'quality';
        qualityInput.value = quality;
        form.appendChild(qualityInput);
        
        // Add platform input
        const platformInput = document.createElement('input');
        platformInput.type = 'hidden';
        platformInput.name = 'platform';
        platformInput.value = detectPlatform(url);
        form.appendChild(platformInput);
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }

    // Helper function to detect platform from URL
    function detectPlatform(url) {
        if (url.includes('youtube.com') || url.includes('youtu.be')) return 'youtube';
        if (url.includes('instagram.com')) return 'instagram';
        if (url.includes('facebook.com')) return 'facebook';
        if (url.includes('twitter.com') || url.includes('x.com')) return 'twitter';
        if (url.includes('whatsapp.com') || url.includes('wa.me')) return 'whatsapp';
        if (url.includes('tiktok.com')) return 'tiktok';
        return 'unknown';
    }

    // Helper function to check if platform is supported
    function isSupportedPlatform(url) {
        const platform = detectPlatform(url);
        return platform !== 'unknown';
    }

    // Simulated data functions
    function getSimulatedTitle(platform) {
        const titles = {
            youtube: 'Amazing YouTube Video',
            instagram: 'Cool Instagram Post',
            facebook: 'Fun Facebook Video',
            twitter: 'Interesting Twitter Video',
            whatsapp: 'WhatsApp Status Media',
            tiktok: 'Trending TikTok Video'
        };
        return titles[platform] || 'Downloadable Media';
    }

    function getSimulatedThumbnail(platform) {
        // In a real app, you would get the actual thumbnail URL from an API
        // These are placeholder images
        const thumbnails = {
            youtube: 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
            instagram: 'https://via.placeholder.com/600x600/FFC0CB/000000?text=Instagram',
            facebook: 'https://via.placeholder.com/600x315/3b5998/ffffff?text=Facebook',
            twitter: 'https://via.placeholder.com/600x335/1da1f2/ffffff?text=Twitter',
            whatsapp: 'https://via.placeholder.com/600x600/25D366/ffffff?text=WhatsApp',
            tiktok: 'https://via.placeholder.com/600x1050/000000/ffffff?text=TikTok'
        };
        return thumbnails[platform] || 'https://via.placeholder.com/600x400';
    }

    function getSimulatedQualities(platform) {
        // Simulated quality options based on platform
        const qualities = {
            youtube: [
                { value: '1080', label: '1080p (HD)' },
                { value: '720', label: '720p (HD)' },
                { value: '480', label: '480p' },
                { value: '360', label: '360p' },
                { value: 'audio', label: 'Audio Only' }
            ],
            instagram: [
                { value: 'high', label: 'High Quality' },
                { value: 'medium', label: 'Medium Quality' }
            ],
            facebook: [
                { value: 'hd', label: 'HD' },
                { value: 'sd', label: 'Standard' }
            ],
            twitter: [
                { value: 'best', label: 'Best Available' }
            ],
            whatsapp: [
                { value: 'original', label: 'Original Quality' }
            ],
            tiktok: [
                { value: 'hd', label: 'HD' },
                { value: 'watermark', label: 'With Watermark' },
                { value: 'nowatermark', label: 'Without Watermark' }
            ]
        };
        return qualities[platform] || [{ value: 'default', label: 'Default Quality' }];
    }
});