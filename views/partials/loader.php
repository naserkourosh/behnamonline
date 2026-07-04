<?php /* Animated page loader shown until the page finishes loading. Self-contained (inline CSS/JS, CSP-safe). */ ?>
<div id="page-loader" aria-hidden="true" role="status" aria-label="در حال بارگذاری">
    <svg width="60" height="60" viewBox="0 0 50 50" fill="none">
        <circle cx="25" cy="25" r="20" stroke="#EAD9DF" stroke-width="5"></circle>
        <circle class="pl-arc" cx="25" cy="25" r="20" stroke="#5C2D46" stroke-width="5" stroke-linecap="round" stroke-dasharray="80 130"></circle>
    </svg>
</div>
<style>
#page-loader{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:#FAF6F0;transition:opacity .45s ease,visibility .45s ease}
#page-loader.is-done{opacity:0;visibility:hidden}
#page-loader .pl-arc{transform-origin:25px 25px;animation:pl-rot .9s linear infinite}
@keyframes pl-rot{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){#page-loader .pl-arc{animation-duration:2s}}
</style>
<script>
(function(){function h(){var l=document.getElementById('page-loader');if(l){l.classList.add('is-done');}}
if(document.readyState==='complete'){h();}else{window.addEventListener('load',h);}
setTimeout(h,4000);})();
</script>
