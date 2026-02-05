import React, { useRef } from "react";
import { motion, useMotionValue, useSpring, useTransform } from "framer-motion";

const TiltCard = ({ title, price, img, delay }) => {
    const ref = useRef(null);

    const x = useMotionValue(0);
    const y = useMotionValue(0);

    const xSpring = useSpring(x);
    const ySpring = useSpring(y);

    const transform = useTransform(
        [xSpring, ySpring],
        ([latestX, latestY]) =>
            `rotateX(${latestY}deg) rotateY(${latestX}deg) scale(1)`
    );

    const handleMouseMove = (e) => {
        if (!ref.current) return;

        const rect = ref.current.getBoundingClientRect();
        const width = rect.width;
        const height = rect.height;

        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        const xPct = mouseX / width - 0.5;
        const yPct = mouseY / height - 0.5;

        x.set(xPct * 20);
        y.set(yPct * -20);
    };

    const handleMouseLeave = () => {
        x.set(0);
        y.set(0);
    };

    return (
        <motion.div
            initial={{ opacity: 0, y: 50 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay }}
            ref={ref}
            onMouseMove={handleMouseMove}
            onMouseLeave={handleMouseLeave}
            style={{
                transformStyle: "preserve-3d",
                transform,
            }}
            className="relative w-full h-96 rounded-xl bg-gradient-to-br from-zinc-800 to-black border border-white/10 cursor-pointer group"
        >
            <div
                style={{
                    transform: "translateZ(50px)",
                    transformStyle: "preserve-3d"
                }}
                className="absolute inset-2 rounded-lg overflow-hidden shadow-2xl bg-black"
            >
                <div className="absolute inset-0 bg-transparent group-hover:bg-black/0 transition-colors z-10" />
                <img
                    src={img}
                    alt={title}
                    className="w-full h-full object-cover transform scale-105 group-hover:scale-110 transition-transform duration-700"
                    onError={(e) => {
                        console.error("Image failed to load:", img);
                        e.target.style.display = 'none';
                        e.target.parentNode.style.backgroundColor = '#333';
                    }}
                />
            </div>

            <div
                style={{
                    transform: "translateZ(75px)",
                }}
                className="absolute bottom-6 left-6 z-20 pointer-events-none"
            >
                <h3 className="text-2xl font-black text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] uppercase italic">{title}</h3>
                <p className="text-cyan-400 font-bold text-lg drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">{price}</p>
            </div>

            {/* Neon Border Glow */}
            <div className="absolute inset-0 rounded-xl transition-opacity duration-300 opacity-0 group-hover:opacity-100 ring-2 ring-pink-500 shadow-[0_0_20px_rgba(236,72,153,0.5)] pointer-events-none" />
        </motion.div>
    );
};

const Services = () => {
    const services = [
        { title: "Corte na Régua", price: "R$ 50,00", img: "/img/servicos/14-06-2022-15-40-01-CORTE-01.png" },
        { title: "Unhas de Gel", price: "R$ 30,00", img: "/img/servicos/14-06-2022-15-38-59-unha-de-gel.png" },
        { title: "Barba Viking", price: "R$ 35,00", img: "/img/servicos/14-06-2022-15-39-39-BARBA-01.png" },
        { title: "Hidratação", price: "R$ 60,00", img: "/img/servicos/14-06-2022-15-39-20-hidratacao.png" },
    ];

    return (
        <section className="py-20 bg-black text-white overflow-hidden perspective-1000 relative">
            <div className="absolute inset-0 bg-[url('/bg-texture.png')] opacity-20 mix-blend-overlay"></div>
            {/* Neon glow patches */}
            <div className="absolute top-1/4 left-0 w-96 h-96 bg-purple-600/20 rounded-full blur-[100px] pointer-events-none" />
            <div className="absolute bottom-1/4 right-0 w-96 h-96 bg-pink-600/20 rounded-full blur-[100px] pointer-events-none" />

            <div className="container mx-auto px-4 relative z-10">
                <motion.h2
                    initial={{ opacity: 0, x: -100 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    className="text-5xl md:text-7xl font-black mb-16 text-center italic"
                >
                    <span className="text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-purple-400 drop-shadow-[0_0_10px_rgba(232,121,249,0.5)]">
                        SERVIÇOS
                    </span>
                </motion.h2>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    {services.map((service, index) => (
                        <TiltCard key={index} {...service} delay={index * 0.1} />
                    ))}
                </div>
            </div>
        </section>
    )
}

export default Services;
