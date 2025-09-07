import darkTheme from '../../../build/theme/sugar-dark.css' assert { type: 'css' };

const darkComponent = {
  'background-base': 'var(--gray-950)',
  'foreground-base': 'var(--gray-800)',
  'border-base': 'var(--gray-700)',
  'button-primary-background': 'var(--blue-400)',
  'button-primary-text': 'var(--gray-800)',
  'button-secondary-background': 'var(--transparent)',
  'button-secondary-text': 'var(--blue-300)',
  'sicon': 'var(--gray-500)',
  'text-base': 'var(--gray-300)',
  'text-action': 'var(--blue-300)',
};

describe('Dark theme component properties', () => {
  test('all properties exist', () => {
    Object.keys(darkComponent).forEach(e => {
      expect(darkComponent[e]).toEqual(darkTheme[`--${e}`]);
    });
  });
});
