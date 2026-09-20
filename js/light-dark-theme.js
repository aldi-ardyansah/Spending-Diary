    (function () {
        const Theme = localStorage.getItem("SpendingDiaryTheme") || "light";
        document.documentElement.setAttribute("data-bs-theme", Theme);
})();

(function () {
    const ThemeToggle = document.getElementById("ThemeToggle");
    const ThemeIcon = document.getElementById("ThemeIcon");

    function updateThemeIcon() {
        const Theme = document.documentElement.getAttribute("data-bs-theme") || "light";

        if (Theme === "dark") {
            ThemeIcon.textContent = "☀";
            ThemeToggle.setAttribute("aria-label", "Switch to light mode");
            ThemeToggle.setAttribute("title", "Switch to light mode");
        } else {
            ThemeIcon.textContent = "☾";
            ThemeToggle.setAttribute("aria-label", "Switch to dark mode");
            ThemeToggle.setAttribute("title", "Switch to dark mode");
        }
    }

    if (ThemeToggle) {
        ThemeToggle.addEventListener("click", function () {
            const CurrentTheme = document.documentElement.getAttribute("data-bs-theme") || "light";
            const NewTheme = CurrentTheme === "dark" ? "light" : "dark";

            document.documentElement.setAttribute("data-bs-theme", NewTheme);
            localStorage.setItem("SpendingDiaryTheme", NewTheme);
            updateThemeIcon();
        });

        updateThemeIcon();
    }
})();