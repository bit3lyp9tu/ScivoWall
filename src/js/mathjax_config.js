window.MathJax = {
    startup: {
        pageReady: () => {
            console.info('Running MathJax');
            return MathJax.startup.defaultPageReady();
        }
    },
    tex: {
        inlineMath: [['\\(', '\\)']],
        displayMath: [['$$', '$$']],
        skipTags: ['script', 'noscript', 'style', 'textarea', 'pre'],
        processEnvironments: true,
        processRefs: true
    },
    svg: {
        fontCache: 'global'
    }
};
