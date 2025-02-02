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
function openSvg(svg) {
    // convert to a valid XML source
    const as_text = new XMLSerializer().serializeToString(svg);
    // store in a Blob
    const blob = new Blob([as_text], { type: "image/svg+xml" });
    // create an URI pointing to that blob
    const url = URL.createObjectURL(blob);
    const win = open(url);
    // so the Garbage Collector can collect the blob
    win.onload = (evt) => URL.revokeObjectURL(url);
};


var svgs = document.querySelectorAll(".phpdocumentor-uml-diagram svg");
for( var i=0,il = svgs.length; i< il; i ++ ) {
    svgs[i].onclick = (evt) => openSvg(evt.target);
}