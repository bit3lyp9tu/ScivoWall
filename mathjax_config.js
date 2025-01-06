MathJax.Hub.Config({
    tex2jax: {
        inlineMath: [['\\(', '\\)']],
        displayMath: [['$$', '$$]']],
        processEscapes: true,
        skipTags: ['script', 'noscript', 'style', 'textarea', 'pre'],
        processEnvironments: true,
        processRefs: true
    },
    jax: ["input/TeX", "output/SVG"],
});

MathJax.Hub.Queue(function () {
    var all = MathJax.Hub.getAllJax(), i;
    //log(all);
    for (i = 0; i < all.length; i += 1) {
        all[i].SourceElement().parentNode.className += ' has-jax';
    }
});
