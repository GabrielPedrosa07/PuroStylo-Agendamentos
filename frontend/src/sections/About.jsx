import React from 'react';
import { motion } from 'framer-motion';

const About = () => {
    return (
        <section className="py-20 bg-black text-white relative overflow-hidden">
            {/* Decorative circle */}
            <div className="absolute -left-20 top-20 w-96 h-96 bg-pink-600/20 rounded-full blur-[128px] pointer-events-none"></div>
            <div className="absolute -right-20 bottom-20 w-96 h-96 bg-cyan-600/20 rounded-full blur-[128px] pointer-events-none"></div>

            <div className="container mx-auto px-4 flex flex-col md:flex-row items-center gap-12 relative z-10">
                <motion.div
                    initial={{ opacity: 0, x: -50 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: true }}
                    className="flex-1"
                >
                    <div className="relative group">
                        <div className="absolute inset-0 bg-gradient-to-tr from-pink-500 to-purple-500 rounded-2xl transform translate-x-4 translate-y-4 group-hover:translate-x-2 group-hover:translate-y-2 transition-transform duration-500"></div>
                        <div className="absolute inset-0 bg-gradient-to-tr from-cyan-500 to-blue-500 rounded-2xl transform -translate-x-4 -translate-y-4 group-hover:-translate-x-2 group-hover:-translate-y-2 transition-transform duration-500 opacity-50"></div>

                        <div className="relative rounded-2xl overflow-hidden aspect-[4/3] border-2 border-white/10">
                            <img src="/images/about-img.jpg" alt="Sobre o Salão" className="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-700" />
                        </div>
                    </div>
                </motion.div>

                <motion.div
                    initial={{ opacity: 0, x: 50 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: true }}
                    className="flex-1"
                >
                    <h2 className="text-4xl md:text-5xl font-black mb-6 italic">
                        <span className="text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-cyan-400">
                            SOBRE NÓS
                        </span>
                    </h2>
                    <p className="text-lg text-gray-300 mb-6 leading-relaxed font-light tracking-wide">
                        Bem-vindo ao <span className="text-pink-400 font-bold">Puro Stylo</span>. Mais que um salão, um refúgio urbano de estética e bem-estar.
                    </p>
                    <p className="text-gray-400 mb-8 leading-relaxed">
                        Nossa missão é realçar sua identidade com técnicas de ponta e um atendimento exclusivo, inspirado nas tendências mais quentes do mundo.
                    </p>

                    <button className="px-8 py-3 bg-white/5 hover:bg-white/10 border border-pink-500/50 text-pink-400 hover:text-pink-300 font-bold tracking-widest uppercase transition-all hover:shadow-[0_0_20px_rgba(236,72,153,0.3)]">
                        Conheça o Espaço
                    </button>
                </motion.div>
            </div>
        </section>
    );
};

export default About;
