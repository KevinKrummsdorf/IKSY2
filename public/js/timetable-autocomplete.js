function debounce(fn, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('input.subject-input');
    const datalist = document.getElementById('course-list');

    const updateList = names => {
        datalist.innerHTML = '';
        names.forEach(n => {
            const opt = document.createElement('option');
            opt.value = n;
            datalist.appendChild(opt);
        });
    };

    const fetchNames = debounce(async term => {
        if (term.length < 2) return;
        try {
            const resp = await fetch(`${baseUrl}/api/courses/autocomplete.php?query=${encodeURIComponent(term)}`);
            if (resp.ok) {
                const names = await resp.json();
                updateList(names);
            }
        } catch (_) {}
    }, 300);

    inputs.forEach(inp => {
        inp.addEventListener('input', e => {
            fetchNames(e.target.value.trim());
        });
    });
});

