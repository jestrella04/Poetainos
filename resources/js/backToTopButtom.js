const headerIsInViewport = el => {
    const scroll = window.scrollY || window.pageYOffset
    const boundsTop = el.getBoundingClientRect().top + scroll

    const viewport = {
        top: scroll,
        bottom: scroll + window.innerHeight,
    }

    const bounds = {
        top: boundsTop,
        bottom: boundsTop + el.clientHeight,
    }

    return (bounds.bottom >= viewport.top && bounds.bottom <= viewport.bottom)
        || (bounds.top <= viewport.bottom && bounds.top >= viewport.top);
}

document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header')
    const backButton = document.querySelector('#back-to-top-wrapper')

    if (null !== header && null !== backButton) {
        const handler = () => raf(() => {
            if (headerIsInViewport(header)) {
                backButton.classList.remove('fade-in')
                backButton.classList.add('fade-out')
            } else {
                backButton.classList.remove('fade-out')
                backButton.classList.add('fade-in')
            }
        })

        handler()
        window.addEventListener('scroll', handler)
    }
});

const raf =
    window.requestAnimationFrame ||
    window.webkitRequestAnimationFrame ||
    window.mozRequestAnimationFrame ||
    function (callback) {
        window.setTimeout(callback, 1000 / 60)
}
