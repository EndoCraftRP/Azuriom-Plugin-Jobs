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

    const allowOther = setName('[data-name="allow_other"]', 'allow_other');
    const minVal = setName('[data-name="min"]', 'min');
    const maxVal = setName('[data-name="max"]', 'max');
    const regexVal = setName('[data-name="regex"]', 'regex');
    const htmlVal = setName('[data-name="html"]', 'html');
    const allowedExtensions = setName('[data-name="allowed_extensions"]', 'allowed_extensions');
    const maxFiles = setName('[data-name="max_files"]', 'max_files');
    const maxSize = setName('[data-name="max_size"]', 'max_size');

    if (id) id.value = item.id ?? '';
    if (label) label.value = item.label ?? '';
    if (type) type.value = item.type ?? 'text';
    if (required) required.checked = item.is_required ?? true;
    if (options) options.value = item.options ?? '';
    if (colMd) colMd.value = item.col_md ?? 12;
    if (allowOther) allowOther.checked = item.allow_other ?? false;
    if (minVal) minVal.value = item.min ?? '';
    if (maxVal) maxVal.value = item.max ?? '';
    if (regexVal) regexVal.value = item.regex ?? '';
    if (htmlVal) htmlVal.value = item.html ?? '';
    if (allowedExtensions) allowedExtensions.value = item.allowed_extensions ?? '';
    if (maxFiles) maxFiles.value = item.max_files ?? '';
    if (maxSize) maxSize.value = item.max_size ?? '';

    const wrapOptions = node.querySelector('.options-wrap');
    const wrapAllowOther = node.querySelector('.allow-other-wrap');
    const wrapValidation = node.querySelector('.validation-wrap');
    const wrapRegex = node.querySelector('.regex-wrap');
    const wrapHtml = node.querySelector('.html-wrap');
    const wrapRequired = node.querySelector('.required-wrap');
    const wrapAttachment = node.querySelector('.attachment-wrap');

    const refresh = () => {
      const valType = type.value;

      // options choice list visibility
      const isSelection = valType === 'select' || valType === 'checkbox' || valType === 'radio';
      if (wrapOptions) wrapOptions.classList.toggle('d-none', !isSelection);
      if (wrapAllowOther) wrapAllowOther.classList.toggle('d-none', !isSelection);

      // min/max limits visibility (for text, textarea, number)
      const isTextOrNum = valType === 'text' || valType === 'textarea' || valType === 'number';
      if (wrapValidation) wrapValidation.classList.toggle('d-none', !isTextOrNum);

      // regex visibility (for text/textarea only)
      const isText = valType === 'text' || valType === 'textarea';
      if (wrapRegex) wrapRegex.classList.toggle('d-none', !isText);

      // HTML block visibility
      const isHtml = valType === 'html';
      if (wrapHtml) wrapHtml.classList.toggle('d-none', !isHtml);
      if (wrapRequired) wrapRequired.classList.toggle('d-none', isHtml);

      // Attachment visibility
      const isAttachment = valType === 'attachment';
      if (wrapAttachment) wrapAttachment.classList.toggle('d-none', !isAttachment);
    };

    type.addEventListener('change', refresh);
    refresh();
    node.querySelector('.remove-field')?.addEventListener('click', () => node.remove());
    builder.appendChild(node);
    index++;
  };

  addBtn.addEventListener('click', () => render());
  (window.fieldsData || []).forEach((f) => render(f));
})();
