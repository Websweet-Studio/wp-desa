const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

// Configuration
const pluginSlug = 'wp-desa';
const mainFile = 'wp-desa.php';
const distDir = 'dist';

// Read version from main file
const mainFileContent = fs.readFileSync(mainFile, 'utf8');
const versionMatch = mainFileContent.match(/Version:\s+([0-9.]+)/);
const version = versionMatch ? versionMatch[1] : '1.0.0';

console.log(`📦 Building ${pluginSlug} v${version}...`);

// Ensure dist directory exists
if (!fs.existsSync(distDir)){
    fs.mkdirSync(distDir);
}

const outputFilename = `${pluginSlug}-${version}.zip`;
const outputPath = path.join(distDir, outputFilename);
const output = fs.createWriteStream(outputPath);
const archive = archiver('zip', {
    zlib: { level: 9 } // Sets the compression level.
});

output.on('close', function() {
    console.log(`✅ Build success!`);
    console.log(`📁 File: ${outputPath}`);
    console.log(`📊 Size: ${(archive.pointer() / 1024 / 1024).toFixed(2)} MB`);
});

archive.on('error', function(err) {
    throw err;
});

archive.pipe(output);

// Files/Directories to include
const filesToInclude = [
    'assets',
    'src',
    'templates',
    'wp-desa.php',
    'README.md',
    'FITUR.md'
];

// Add files to the archive within a folder named after the plugin slug
filesToInclude.forEach(file => {
    const filePath = path.join(__dirname, file);
    if (fs.existsSync(filePath)) {
        const stats = fs.statSync(filePath);
        if (stats.isDirectory()) {
            archive.directory(file, `${pluginSlug}/${file}`);
        } else {
            archive.file(file, { name: `${pluginSlug}/${file}` });
        }
    }
});

archive.finalize();
