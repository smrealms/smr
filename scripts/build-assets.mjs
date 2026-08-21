import { buildSync } from 'esbuild';
import { readdirSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const publicDir = path.join(rootDir, 'src/htdocs');
const assetDir = path.join(rootDir, 'assets');
const assetDirectories = ['css', 'js'];

const entryPoints = assetDirectories.flatMap(assetDirectory => {
	const directory = path.join(publicDir, assetDirectory);
	const entries = readdirSync(directory, { recursive: true });
	return entries
		.filter(entry => ['.css', '.js'].includes(path.extname(entry)))
		.map(entry => path.join(directory, entry));
});

const result = buildSync({
	bundle: true,
	entryPoints,
	entryNames: '[dir]/[name]-[hash]',
	external: ['/images/*', '/css/fonts/*'],
	metafile: true,
	minify: true,
	outbase: publicDir,
	outdir: assetDir,
	target: 'es2017',
});

const manifest = Object.fromEntries(Object.entries(result.metafile.outputs).flatMap(([outputPath, output]) => {
	return [[
		`/${path.relative(publicDir, path.resolve(rootDir, output.entryPoint))}`,
		`/assets/${path.relative(assetDir, path.resolve(rootDir, outputPath))}`,
	]];
}));

writeFileSync(
	path.join(assetDir, 'asset-manifest.json'),
	`${JSON.stringify(manifest, null, '\t')}\n`,
);
