
# Starterkit WP theme (Kickstart WP templates for several customers)
## Features
- basic modules (downloads, slider, headline, intro etc.)
- basic hero modules
- basic search function
- main menu, footer menu
- fully accessible
- fully responsive
- essential images have focus-point

## Read this!
- search whole project for "platzhalter" and replace reasonably
- webfonts of AWO "FagoCo" in /fonts
- example forms for "Contact Form 7": /_forms/*.html (use this as a base otherwise forms CSS will not fit 100%)
- SVG icons are masked – change their color by editing pseudo-elements "background-color" in scss

## AOS options
// Global settings:
disable: false, // accepts following values: 'phone', 'tablet', 'mobile', boolean, expression or function
startEvent: 'DOMContentLoaded', // name of the event dispatched on the document, that AOS should initialize on
initClassName: 'aos-init', // class applied after initialization
animatedClassName: 'aos-animate', // class applied on animation
useClassNames: false, // if true, will add content of `data-aos` as classes on scroll
disableMutationObserver: false, // disables automatic mutations' detections (advanced)
debounceDelay: 50, // the delay on debounce used while resizing window (advanced)
throttleDelay: 99, // the delay on throttle used while scrolling the page (advanced)

// Settings that can be overridden on per-element basis, by `data-aos-*` attributes:
offset: 120, // offset (in px) from the original trigger point
delay: 0, // values from 0 to 3000, with step 50ms
duration: 800, // values from 0 to 3000, with step 50ms
easing: 'ease', // default easing for AOS animations
once: false, // whether animation should happen only once - while scrolling down
mirror: false, // whether elements should animate out while scrolling past them
anchorPlacement: 'top-bottom', // defines which position of the element regarding to window should trigger the animation
