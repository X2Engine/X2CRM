import loadComponents from './components';
import loadBlocks from './blocks';

export default (editor, opts = {}) => {
  const options = { ...{
    // The ID used to create tooltip block and component
    id: 'tooltip',

    // Label of the tooltip. Used for the block and component name
    labelTooltip: 'Tooltip',

    // Object to extend the default tooltip block, eg. { label: 'Tooltip', category: 'Extra', ... }.
    // Pass a falsy value to avoid adding the block
    blockTooltip: {},

    // Object to extend the default tooltip properties, eg. `{ name: 'Tooltip', droppable: false, ... }`
    propsTooltip: {},

    // A function which allows to extend default traits by receiving the original array and returning a new one
    extendTraits: traits => traits,

    // Tooltip attribute prefix
    attrTooltip: 'data-tooltip',

    // Tooltip class prefix
    classTooltip: 'tooltip-component',

    // Custom CSS styles, this will replace the default one
    style: '',

    // Additional CSS styles
    styleAdditional: '',

    // Make all tooltip relative classes private
    privateClasses: 1,

    // Indicate if the tooltip can be styled. You can also pass an array
    // of which proprties can be styled. Eg. `['color', 'background-color']`
    stylableTooltip: [
      'background-color',
      'padding',
      'padding-top',
      'padding-right',
      'padding-bottom',
      'padding-left',
      'font-family',
      'font-size',
      'font-weight',
      'letter-spacing',
      'color',
      'line-height',
      'text-align',
      'border-radius',
      'border-top-left-radius',
      'border-top-right-radius',
      'border-bottom-left-radius',
      'border-bottom-right-radius',
      'border',
      'border-width',
      'border-style',
      'border-color',
    ],

    // If true, force the tooltip to be shown
    showTooltipOnStyle: 1,
  },  ...opts };

  // Add components
  loadComponents(editor, options);

  // Add blocks
  loadBlocks(editor, options);
};
