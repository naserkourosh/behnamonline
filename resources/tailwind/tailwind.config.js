/** @type {import('tailwindcss').Config} */
module.exports = {
  // Globs are resolved from the project root (see tools/build.ps1).
  content: [
    "./views/**/*.php",
    "./app/**/*.php",
    "./public/assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#E8C5C8",
        secondary: { DEFAULT: "#5C2D46", light: "#7c4862" },
        gold: "#C29B45",
        surface: "#FAF6F0",
        cream: "#EBE3DA",
        line: "#F0E6DC",
        line2: "#F2EBE3",
        mauve: "#B58CA0",
        pink: "#FAF0F1",
        ink: "#222222",
        success: "#10B981",
        warning: "#F59E0B",
        danger: "#EF4444",
        star: "#F0A93B",
      },
      fontFamily: {
        sans: ["Vazirmatn", "Tahoma", "system-ui", "sans-serif"],
      },
      maxWidth: {
        page: "1440px",
        mobile: "440px",
      },
      borderRadius: {
        "xl2": "14px",
        "4xl": "26px",
      },
      boxShadow: {
        soft: "0 4px 14px rgba(92,45,70,.06)",
        card: "0 8px 22px rgba(92,45,70,.10)",
        rise: "0 16px 36px rgba(92,45,70,.12)",
        nav: "0 -6px 20px rgba(92,45,70,.06)",
        balloon: "0 20px 50px rgba(92,45,70,.30)",
      },
      keyframes: {
        balloonPop: {
          "0%": { transform: "scale(.4)", opacity: "0" },
          "60%": { transform: "scale(1.05)" },
          "100%": { transform: "scale(1)", opacity: "1" },
        },
        sheetUp: {
          from: { transform: "translateY(100%)" },
          to: { transform: "translateY(0)" },
        },
        pop: {
          "0%": { transform: "scale(.3)", opacity: "0" },
          "60%": { transform: "scale(1.08)" },
          "100%": { transform: "scale(1)", opacity: "1" },
        },
        toastIn: {
          from: { transform: "translateY(20px)", opacity: "0" },
          to: { transform: "translateY(0)", opacity: "1" },
        },
      },
      animation: {
        balloonPop: "balloonPop .28s ease-out",
        sheetUp: "sheetUp .3s ease-out",
        pop: "pop .5s ease-out",
        toastIn: "toastIn .25s ease-out",
      },
    },
  },
  plugins: [],
};
