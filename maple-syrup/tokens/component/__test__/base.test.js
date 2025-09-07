import baseTheme from '../../../build/theme/sugar-base.css' assert { type: 'css' };

const baseComponent = {
  'text-secondary': 'var(--gray-500)',
};

describe('Base theme component properties', () => {
  test('all properties exist', () => {
    Object.keys(baseComponent).forEach(e => {
      expect(baseComponent[e]).toEqual(baseTheme[`--${e}`]);
    });
  });
});
