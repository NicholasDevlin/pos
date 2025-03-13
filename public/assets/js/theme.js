/**
 * Theme: Drezoc - Bootstrap 4 Admin Template
 * Author: Myra Studio
 * File: Main Js
 */

!function(t) {
    "use strict";

    // Initialize MetisMenu for the sidebar
    t("#side-menu").metisMenu();

    // Check if the menu state is saved in localStorage
    const menuState = localStorage.getItem('verticalMenuState');
    if (menuState === 'opened' && window.innerWidth > 992) {
        t(".vertical-menu, #page-topbar, .main-content, .footer").removeClass("menu-hidden");
    } else {
        t(".vertical-menu, #page-topbar, .main-content, .footer").addClass("menu-hidden");
    }

    // Toggle vertical menu behavior
    t("#vertical-menu-btn").on("click", function() {
        if (window.innerWidth > 992) {
            t(".vertical-menu, #page-topbar, .main-content, .footer").toggleClass("menu-hidden");

            // Save the current state to localStorage
            if (t(".vertical-menu").hasClass("menu-hidden")) {
                localStorage.setItem('verticalMenuState', 'closed');
            } else {
                localStorage.setItem('verticalMenuState', 'opened');
            }
        } else {
            t("body").toggleClass("enable-vertical-menu");
        }
    });

    // Close vertical menu when clicking on overlay
    t(".menu-overlay").on("click", function() {
        t("body").removeClass("enable-vertical-menu");
    });

    // Set the active class for sidebar menu links based on current page
    t("#sidebar-menu a").each(function() {
        const a = window.location.href.split(/[?#]/)[0];
        if (a.startsWith(this.href)) {
            t(this).addClass("active");
            t(this).parent().addClass("mm-active");
            t(this).parent().parent().addClass("mm-show");
            t(this).parent().parent().prev().addClass("mm-active");
            t(this).parent().parent().parent().addClass("mm-active");
            t(this).parent().parent().parent().parent().addClass("mm-show");
            t(this).parent().parent().parent().parent().parent().addClass("mm-active");
        }
    });

    // Initialize Bootstrap tooltips and popovers
    t(function() {
        t('[data-toggle="tooltip"]').tooltip();
        t('[data-toggle="popover"]').popover();
    });

}(jQuery);
