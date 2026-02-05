import React, { useRef, useEffect } from 'react';
import { motion, useScroll, useTransform } from 'framer-motion';

const Hero = () => {
    const containerRef = useRef(null);
    const { scrollY } = useScroll();
    const y = useTransform(scrollY, [0, 500], [0, 200]);
    const opacity = useTransform(scrollY, [0, 300], [1, 0]);

    return (
        <section ref={containerRef} className="relative h-screen w-full overflow-hidden bg-black m-0 p-0">
            {/* Background Video - Absolute Zero Z-Index */}
            <div className="absolute inset-0 w-full h-full z-0">
                <div className="absolute inset-0 bg-black/40 z-10" /> {/* Dark overlay */}
                <video
                    autoPlay
                    loop
                    muted
                    playsInline
                    className="w-full h-full object-cover"
                >
                    <source src="/videos/hero-bg.mp4" type="video/mp4" />
                </video>
            </div>

            {/* Content using "GTA VI" style typography */}
            <div className="relative z-20 h-full flex flex-col items-center justify-center text-center px-4">
                <motion.div
                    initial={{ opacity: 0, scale: 1.2 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 1.5, ease: "easeOut" }}
                >
                    {/* GTA VI Style Logo */}
                    <h1 className="text-7xl md:text-9xl font-black italic tracking-tighter drop-shadow-2xl relative inline-block"
                        style={{ fontFamily: 'Impact, sans-serif' }}>

                        {/* Text Gradient */}
                        <span className="bg-clip-text text-transparent bg-gradient-to-b from-[#e0adff] via-[#d672ff] to-[#9900ff]"
                            style={{
                                filter: "drop-shadow(0 0 10px rgba(214, 114, 255, 0.5))"
                            }}>
                            PURO
                        </span>
                        <span className="bg-clip-text text-transparent bg-gradient-to-b from-[#c3f0ff] via-[#4ecdc4] to-[#007f7f] ml-4"
                            style={{
                                filter: "drop-shadow(0 0 10px rgba(78, 205, 196, 0.5))"
                            }}>
                            STYLO
                        </span>

                        {/* Outline Effect (Simulated) */}
                        <span className="absolute inset-0 text-transparent border-black stroke-2" style={{ WebkitTextStroke: '2px black', zIndex: -1 }}>
                            PURO STYLO
                        </span>
                    </h1>

                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 1, duration: 1 }}
                        className="mt-6 inline-block bg-black/60 backdrop-blur-md border border-pink-500/30 px-8 py-2 rounded-full"
                    >
                        <p className="text-pink-100 text-xl font-bold tracking-[0.3em] uppercase">
                            Vice City Vibes
                        </p>
                    </motion.div>
                </motion.div>

                <motion.button
                    initial={{ opacity: 0, y: 50 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 1.5 }}
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    className="mt-12 px-10 py-4 bg-gradient-to-r from-[#d672ff] to-[#4ecdc4] text-black font-black text-lg uppercase tracking-wider rounded-lg shadow-[0_0_20px_rgba(214,114,255,0.6)] hover:shadow-[0_0_40px_rgba(78,205,196,0.8)] transition-all"
                >
                    Agendar Horário
                </motion.button>
            </div>

            {/* Decorative "GPS" or HUD elements */}
            <div className="absolute bottom-10 right-10 z-20 hidden md:block">
                <div className="flex flex-col items-end text-white/60 font-mono text-xs gap-1">
                    <span>LOC: 23.5505° S, 46.6333° W</span>
                    <span>WEATHER: SUNNY</span>
                </div>
            </div>
        </section>
    );
};

export default Hero;
