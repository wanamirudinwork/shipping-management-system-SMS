/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 * 
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

import StyleDictionary from 'style-dictionary';
import { LICENSE } from './license.js';

console.log('Build started...');
console.log('\n==============================================');

const {
  fileHeader,
} = StyleDictionary.formatHelpers;

/**
 * Custom SugarCRM license file header
 */
StyleDictionary.registerFileHeader({
  name: 'SugarCRM license',
  fileHeader: () => {
	return LICENSE.split('\n');
  },
});

/**
 * Custom output file formats
 */

/**
 * Create a standard JSON file for adding palette into Tailwind CSS config.
 * For example, a token will look like this:
 * {
 *   value: '#155f75',
 *   group: 'color/base',
 *   filePath: 'tokens/color/base.json',
 *   isSource: true,
 *   original: { value: 'hsl(194,70,27)', group: 'color/base' },
 *   name: '800',
 *   attributes: { category: 'color', type: 'base', item: 'cyan', subitem: '800' },
 *   path: [ 'color', 'base', 'cyan', '800' ]
 * },
 */
StyleDictionary.registerFormat({
  name: 'tailwind/js', 
  formatter: ({dictionary, file}) => {
	const palette = {};
	const allTokens = dictionary.allProperties;

	for (let i = 0; i < allTokens.length; i++) {
	  let currentToken = allTokens[i];
	  let nestedName = currentToken?.name.split('-');
	  let value = currentToken?.comment.includes('original') ? currentToken.original.value : currentToken.value;

	  if (nestedName.length > 1) {
		if (!palette[nestedName[0]]) {
		  palette[nestedName[0]] = {};
		}
      palette[nestedName[0]][nestedName[1]] = value;
	  } else {
      palette[currentToken?.name] = value;
	  }
	}

    return `${fileHeader({file})} export default ${JSON.stringify(palette, null, 2)};`
  }
});

/**
 * Create a standard JSON file for adding palette into Tailwind CSS config. Using the minimized
 * naming scheme found for properties like border radius.
 * For example, a token will look like this:
 * {
 *   value: '#155f75',
 *   group: 'color/base',
 *   filePath: 'tokens/color/base.json',
 *   isSource: true,
 *   original: { value: 'hsl(194,70,27)', group: 'color/base' },
 *   name: '800',
 *   attributes: { category: 'color', type: 'base', item: 'cyan', subitem: '800' },
 *   path: [ 'color', 'base', 'cyan', '800' ]
 * },
 */
StyleDictionary.registerFormat({
  name: 'tailwind/js/minimized', 
  formatter: ({dictionary, file}) => {
    const palette = {};
    const allTokens = dictionary.allProperties;
    
    for (let i = 0; i < allTokens.length; i++) {
      let currentToken = allTokens[i];
      let nestedName = currentToken?.name.split('-');
      let value = currentToken?.comment.includes('original') ? currentToken.original.value : currentToken.value;

      if (nestedName.length > 1) {
        if (!palette[nestedName[0]]) {
          palette[nestedName[0]] = {};
        }
        palette[nestedName[0]][nestedName[1]] = value;
      } else {
        palette[currentToken?.name] = value;
      }
    }

    return `${fileHeader({file})} export default ${JSON.stringify(palette, null, 2)};`
  }
});

/**
 * Create a standard JSON file for adding palette into Tailwind CSS config. Using the minimized
 * naming scheme found for properties like border radius.
 * For example, a token will look like this:
 * {
 *   value: '#155f75',
 *   group: 'color/base',
 *   filePath: 'tokens/color/base.json',
 *   isSource: true,
 *   original: { value: 'hsl(194,70,27)', group: 'color/base' },
 *   name: '800',
 *   attributes: { category: 'color', type: 'base', item: 'cyan', subitem: '800' },
 *   path: [ 'color', 'base', 'cyan', '800' ]
 * },
 */
StyleDictionary.registerFormat({
  name: 'tailwind/js/minimized', 
  formatter: ({dictionary, file}) => {
    const palette = {};
    const allTokens = dictionary.allProperties;
    
    for (let i = 0; i < allTokens.length; i++) {
      let currentToken = allTokens[i];
      palette[currentToken?.name_min] = currentToken.value;     
    }

    return `${fileHeader({file})} export default ${JSON.stringify(palette, null, 2)};`
  }
});
/**
 * Transform groups
 */

StyleDictionary.registerTransformGroup({
  name: 'custom/tailwindcss',
  transforms: ['attribute/cti', 'color/hex']
});

// This transformGroup is a modified version of the `css` group, but removes the name/cti/kebab transform.
StyleDictionary.registerTransformGroup({
  name: 'custom/theme/variables',
  transforms: ['attribute/cti', 'color/hex']
});

/**
 * Custom filters
 */

// Filter to only grab tokens with group: palette attribute.
StyleDictionary.registerFilter({
  name: 'isBaseColor',  
  matcher: (token) => {  
    return token?.group === 'color/base';
  }
});

// Filter to grab tokens that are for base styles.
StyleDictionary.registerFilter({
  name: 'isBase',
  matcher: (token) => {
    return token?.group.includes('/base');
  }
});

// Filter to grab tokens that are for light mode.
StyleDictionary.registerFilter({
  name: 'isLight',
  matcher: (token) => {
    return token?.group === 'component/light';
  }
});

// Filter to grab tokens that are for dark mode.
StyleDictionary.registerFilter({
  name: 'isDark',
  matcher: (token) => {
    return token?.group === 'component/dark';
  }
});

// Filter to grab tokens that are base sizing values.
StyleDictionary.registerFilter({
  name: 'isBaseSize',
  matcher: (token) => {
    return token?.group === 'size/base';
  }
});

const StyleDictionaryExtended = StyleDictionary.extend('./config.json');

// Build ALL the platforms. 
StyleDictionaryExtended.buildAllPlatforms();

console.log('\n==============================================');
console.log('\nBuild completed!');
