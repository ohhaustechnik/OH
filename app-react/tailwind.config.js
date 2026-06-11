export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        bg: '#070a12', card: '#0e131d', card2: '#131a27',
        line: '#1e2940', accent: '#39d6ff', accent2: '#1693c4',
        muted: '#7e93ad', txt: '#dfeaf6',
        ok: '#34e09a', warn: '#e7b14b', bad: '#ff5d6c'
      },
      borderRadius: { xl2: '20px' },
      fontFamily: { sans: ['-apple-system','BlinkMacSystemFont','SF Pro Display','Segoe UI','Roboto','sans-serif'] }
    }
  },
  plugins: []
}
