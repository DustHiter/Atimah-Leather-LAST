document.addEventListener('DOMContentLoaded', () => {
    
    // Initialize AOS (Animate on Scroll)
    AOS.init({
        duration: 800, // Animation duration in ms
        offset: 100,   // Offset (in px) from the original trigger point
        once: true,    // Whether animation should happen only once - while scrolling down
    });

    // Add a class to the header when the page is scrolled
    const header = document.querySelector('.site-header');
    if (header) {
        const scrollThreshold = 50; // Pixels to scroll before adding the class

        const handleScroll = () => {
            if (window.scrollY > scrollThreshold) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        };

        // Listen for the scroll event
        window.addEventListener('scroll', handleScroll);
        
        // Initial check in case the page is already scrolled on load
        handleScroll();
    }

});