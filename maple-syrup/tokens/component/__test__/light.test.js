import lightTheme from '../../../build/theme/sugar-light.css' assert { type: 'css' };

const lightComponent = {
  'background-base': 'var(--gray-100)',
  'foreground-base': 'var(--white)',
  'border-base': 'var(--gray-300)',
  'button-primary-background': 'var(--blue-600)',
  'button-primary-text': 'var(--white)',
  'button-secondary-background': 'var(--transparent)',
  'button-secondary-text': 'var(--blue-600)',
  'sicon': 'var(--gray-400)',
  'text-base': 'var(--gray-800)',
  'text-action': 'var(--blue-600)',
};

describe('Light theme component properties', () => {
  test('all properties exist', () => {
    Object.keys(lightComponent).forEach(e => {
      expect(lightComponent[e]).toEqual(lightTheme[`--${e}`]);
    });
  });
});
