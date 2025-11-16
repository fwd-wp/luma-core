import { src, dest, watch, series, parallel } from 'gulp';

import zip from "gulp-zip";
import { deleteAsync } from 'del';
import pkg from "./package.json" with { type: "json" };
import { writeFile } from 'fs/promises';

// not used but available
const isProduction = process.env.NODE_ENV === 'production';

// cleans everything
export const clean = () => deleteAsync(['public', 'build', 'style.css']);
// keeps build folder in tact, after wordpress build
export const cleanBuild = () => deleteAsync(['public', 'style.css']);

const createThemeStyle = () => {
  const theme = pkg.themeMeta || {};
  return `/*
Theme Name: ${theme.themeName || pkg.name}
Theme URI: ${pkg.homepage || ''}
Author: ${pkg.author}
Author URI: ${theme.authorURI || ''}
Description: ${pkg.description}
Requires at least: ${theme.RequiresAtLeast}
Tested up to: ${theme.TestedUpTo}
Requires PHP: ${theme.RequiresPHP}
Version: ${pkg.version}
License: ${pkg.license}
License URI: ${theme.licenseURI}
Text Domain: ${pkg.name}
Tags: ${(pkg.keywords || []).join(', ')}
*/\n`;
};

export const writeStyle = async () => {
  const content = createThemeStyle();
  await writeFile('style.css', content, 'utf8');
};

const copy = () => {
  return src([
    './*.php',
    './style.css',
    'assets/**',
    'build/**',
    'languages/**',
    'src/**',
    'vendor/**',
  ], { base: '.' })
    .pipe(dest(`public/${pkg.name}`));
};

const compress = () => {
  return src([`public/${pkg.name}/**/*`], { base: 'public' })
    .pipe(zip(`${pkg.name}.zip`))
    .pipe(dest('public'));
};


export const build = series(cleanBuild, writeStyle, copy, compress);
export const buildRepo = series(cleanBuild, writeStyle, copy);