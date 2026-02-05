import React from 'react';
import Hero from './sections/Hero';
import Services from './sections/Services';
import Products from './sections/Products';
import About from './sections/About';
import Testimonials from './sections/Testimonials';
import Contact from './sections/Contact';

function App() {
  return (
    <div className="bg-black text-white min-h-screen font-sans selection:bg-emerald-500 selection:text-white">
      <Hero />
      <Services />
      <Products />
      <About />
      <Testimonials />
      <Contact />
      <footer className="py-8 text-center text-zinc-600 text-sm">
        <p>&copy; {new Date().getFullYear()} Puro Stylo. Todos os direitos reservados.</p>
      </footer>
    </div>
  );
}

export default App;
