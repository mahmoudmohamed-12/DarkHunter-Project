/* Search */
const searchInput = document.getElementById("searchInput");
const filterSelect = document.getElementById("filterSelect");
const labs = document.querySelectorAll(".lab-card");

function filterLabs() {
  const search = searchInput.value.toLowerCase();
  const filter = filterSelect.value;

  labs.forEach((lab) => {
    const title = lab.querySelector("h3").innerText.toLowerCase();

    const matchesSearch = title.includes(search);
    const matchesFilter = filter === "all" || lab.classList.contains(filter);

if (matchesSearch && matchesFilter) {
      lab.classList.remove("hide-lab");
      lab.classList.add("show");
    } else {
      lab.classList.add("hide-lab");
      lab.classList.remove("show");
    }
  });
}

searchInput.addEventListener("input", filterLabs);
filterSelect.addEventListener("change", filterLabs);

/* Animation on scroll */
const elements = document.querySelectorAll(".lab-card");

window.addEventListener("scroll", () => {
  elements.forEach((el) => {
    const position = el.getBoundingClientRect().top;
    const screenHeight = window.innerHeight;

    if (position < screenHeight - 50) {
      el.classList.add("show");
    }
  });
});
