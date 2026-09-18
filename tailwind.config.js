/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./templates/**/*.php",
    "./app/functions/**/*.php",
    "./assets/js/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        edomex: {
          guinda: {
            DEFAULT: '#9F2241',
            dark: '#7A1A32',
            light: '#B73252',
            50: '#FBF4F5',
            100: '#F5E4E8'
          },
          cafe: {
            DEFAULT: '#965F36',
            dark: '#7A4B29',
            light: '#B2764A'
          },
          oro: {
            DEFAULT: '#BC955B',
            dark: '#9F7C44',
            light: '#D4B483'
          },
          arena: {
            DEFAULT: '#DDC8A4',
            dark: '#C8B08A',
            light: '#F8F4EC',
            50: '#FDFBF7'
          }
        },
        riesgo: {
          nulo: '#00A86B',
          bajo: '#3B82F6',
          medio: '#F59E0B',
          alto: '#F97316',
          muyalto: '#DC2626'
        }
      },
      fontFamily: {
        title: ['Montserrat', 'Gotham', 'sans-serif'],
        sans: ['Inter', 'BW Modelica', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif']
      },
      boxShadow: {
        card: '0 4px 20px -2px rgba(159, 34, 65, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04)',
        'card-hover': '0 10px 25px -5px rgba(159, 34, 65, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.05)'
      }
    },
  },
  plugins: [],
}

