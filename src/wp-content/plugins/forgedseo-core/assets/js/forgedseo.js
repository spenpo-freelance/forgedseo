(function () {
  "use strict";

  function headerEl() {
    return (
      document.querySelector(".wp-site-blocks > header") ||
      document.querySelector("header.wp-block-template-part") ||
      document.querySelector(".wp-site-blocks > .wp-block-template-part")
    );
  }

  function initHeaderScroll() {
    var header = headerEl();
    if (!header) {
      return;
    }

    function syncHeight() {
      document.documentElement.style.setProperty(
        "--fseo-header-height",
        header.offsetHeight + "px"
      );
    }

    function update() {
      header.classList.toggle("is-scrolled", window.scrollY > 16);
    }

    function sync() {
      update();
      syncHeight();
    }

    sync();
    window.addEventListener("scroll", sync, { passive: true });
    window.addEventListener("resize", sync);
  }

  initHeaderScroll();
})();
