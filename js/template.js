(function(){
    window.addEventListener('load', () => {
        const el = document.querySelector('.phpdocumentor-on-this-page__content')
        if (!el) {
            return;
        }

        const observer = new IntersectionObserver(
            ([e]) => {
                e.target.classList.toggle("-stuck", e.intersectionRatio < 1);
            },
            {threshold: [1]}
        );

        observer.observe(el);
    })
})();
