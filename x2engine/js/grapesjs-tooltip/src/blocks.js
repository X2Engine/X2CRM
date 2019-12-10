export default (editor, opts = {}) => {
  const { blockTooltip, labelTooltip, id } = opts;

  blockTooltip && editor.BlockManager.add(id, {
    label: `<svg viewBox="0 0 24 24">
        <path d="M4 2h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2h-4l-4 4-4-4H4c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2m0 2v12h4.83L12 19.17 15.17 16H20V4H4z"></path>
      </svg>
      <div>${labelTooltip}</div>`,
    category: 'Extra',
    select: true,
    content: { type: id },
    ...blockTooltip
  });
}
