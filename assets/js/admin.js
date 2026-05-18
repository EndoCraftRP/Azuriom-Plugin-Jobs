(() => {
  const builder = document.getElementById('fields-builder');
  const addBtn = document.getElementById('add-field');
  const tpl = document.getElementById('field-template');
  if (!builder || !addBtn || !tpl) return;

  let index = 0;
  const render = (item = {}) => {
    const node = tpl.content.firstElementChild.cloneNode(true);
    const setName = (selector, name) => {
      const el = node.querySelector(selector);
      if (!el) return null;
      el.name = `fields[${index}][${name}]`;
      return el;
    };
    const id = setName('[data-name="id"]', 'id');
    const label = setName('[data-name="label"]', 'label');
    const type = setName('[data-name="type"]', 'type');
    const required = setName('[data-name="is_required"]', 'is_required');
    const options = setName('[data-name="options"]', 'options');
    const colMd = setName('[data-name="col_md"]', 'col_md');
    if (id) id.value = item.id ?? '';
    if (label) label.value = item.label ?? '';
    if (type) type.value = item.type ?? 'text';
    if (required) required.checked = item.is_required ?? true;
    if (options) options.value = item.options ?? '';
    if (colMd) colMd.value = item.col_md ?? 12;
    const wrap = node.querySelector('.options-wrap');
    const refresh = () => wrap.classList.toggle('d-none', type.value !== 'select');
    type.addEventListener('change', refresh);
    refresh();
    node.querySelector('.remove-field')?.addEventListener('click', () => node.remove());
    builder.appendChild(node);
    index++;
  };

  addBtn.addEventListener('click', () => render());
  (window.fieldsData || []).forEach((f) => render(f));
})();
