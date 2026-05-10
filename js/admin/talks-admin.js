(function () {
    'use strict';

    const container = document.getElementById('fabian-resources');
    const input = document.getElementById('fabian_session_resources_input');
    if (!container || !input) return;

    let items = [];
    try {
        const parsed = JSON.parse(container.dataset.initial || '[]');
        if (Array.isArray(parsed)) {
            items = parsed
                .filter((i) => i && typeof i === 'object' && i.url)
                .map((i) => ({ type: 'link', label: i.label || '', url: i.url || '' }));
        }
    } catch (e) {
        items = [];
    }

    function render() {
        container.innerHTML = '';

        if (items.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'description';
            empty.textContent = 'No links yet. Use the button below to add one.';
            container.appendChild(empty);
            return;
        }

        const table = document.createElement('table');
        table.className = 'widefat striped';
        table.style.marginBottom = '10px';

        const thead = document.createElement('thead');
        thead.innerHTML =
            '<tr>' +
            '<th scope="col" style="width:30%;">Label</th>' +
            '<th scope="col">URL</th>' +
            '<th scope="col" style="width:80px;"></th>' +
            '</tr>';
        table.appendChild(thead);

        const tbody = document.createElement('tbody');

        items.forEach((item, index) => {
            const row = document.createElement('tr');

            const labelCell = document.createElement('td');
            const labelInput = document.createElement('input');
            labelInput.type = 'text';
            labelInput.className = 'widefat';
            labelInput.value = item.label || '';
            labelInput.setAttribute('aria-label', 'Label');
            labelInput.addEventListener('input', (e) => {
                items[index].label = e.target.value;
                sync();
            });
            labelCell.appendChild(labelInput);

            const urlCell = document.createElement('td');
            const urlInput = document.createElement('input');
            urlInput.type = 'url';
            urlInput.className = 'widefat';
            urlInput.placeholder = 'https://';
            urlInput.value = item.url || '';
            urlInput.setAttribute('aria-label', 'URL');
            urlInput.addEventListener('input', (e) => {
                items[index].url = e.target.value;
                sync();
            });
            urlCell.appendChild(urlInput);

            const actionCell = document.createElement('td');
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'button button-small button-link-delete';
            remove.textContent = 'Remove';
            remove.addEventListener('click', () => {
                items.splice(index, 1);
                render();
                sync();
            });
            actionCell.appendChild(remove);

            row.appendChild(labelCell);
            row.appendChild(urlCell);
            row.appendChild(actionCell);
            tbody.appendChild(row);
        });

        table.appendChild(tbody);
        container.appendChild(table);
    }

    function sync() {
        input.value = JSON.stringify(items);
    }

    document.querySelectorAll('[data-fabian-resource-add]').forEach((btn) => {
        btn.addEventListener('click', () => {
            items.push({ type: 'link', label: '', url: '' });
            render();
            sync();
        });
    });

    render();
    sync();
})();
