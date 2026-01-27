import { writable } from "svelte/store";

export type Theme = "light" | "dim" | "dark";

const getInitialTheme = (): Theme => {
  if (typeof window !== "undefined") {
    const savedTheme = localStorage.getItem("theme") as Theme;
    if (savedTheme && ["light", "dim", "dark"].includes(savedTheme)) {
      return savedTheme;
    }
  }
  return "dark"; // Default to dark
};

export const theme = writable<Theme>(getInitialTheme());

if (typeof window !== "undefined") {
  theme.subscribe((value) => {
    localStorage.setItem("theme", value);

    // Apply theme to document element
    const root = document.documentElement;
    root.classList.remove("light", "dim", "dark");
    root.classList.add(value);
  });
}
