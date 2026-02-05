import React from 'react';
import { motion } from 'framer-motion';

const testimonials = [
    { name: "Ana Silva", text: "O melhor corte que já fiz! A equipe é incrível e o ambiente é super moderno.", img: "/img/comentarios/sem-foto.jpg" },
    { name: "Beatriz Costa", text: "Atendimento vip, saí me sentindo uma nova mulher. Recomendo muito!", img: "/img/comentarios/sem-foto.jpg" },
    { name: "Carla Souza", text: "Profissionais qualificados e produtos de primeira linha. Amei!", img: "/img/comentarios/sem-foto.jpg" },
    { name: "Daniela Oliveira", text: "Ambiente agradável e serviço impecável. Voltarei com certeza.", img: "/img/comentarios/sem-foto.jpg" },
];

const TestimonialCard = ({ data }) => (
    <div className="min-w-[300px] md:min-w-[400px] bg-zinc-900/50 backdrop-blur-md border border-white/5 p-6 rounded-2xl mx-4 flex flex-col gap-4">
        <div className="flex items-center gap-4">
            <div className="w-12 h-12 rounded-full overflow-hidden bg-zinc-800">
                <img src={data.img} alt={data.name} className="w-full h-full object-cover" />
            </div>
            <div>
                <h4 className="font-bold text-white">{data.name}</h4>
                <div className="flex text-yellow-500 text-xs">★★★★★</div>
            </div>
        </div>
        <p className="text-zinc-400 italic">"{data.text}"</p>
    </div>
);

const Testimonials = () => {
    return (
        <section className="py-20 bg-black overflow-hidden relative">
            <div className="absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-black to-transparent z-10" />
            <div className="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black to-transparent z-10" />

            <div className="container mx-auto px-4 mb-12 text-center">
                <h2 className="text-4xl md:text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-cyan-400">
                    O QUE DIZEM
                </h2>
            </div>

            <div className="flex">
                <motion.div
                    animate={{ x: [0, -1000] }}
                    transition={{
                        repeat: Infinity,
                        ease: "linear",
                        duration: 20
                    }}
                    className="flex"
                >
                    {[...testimonials, ...testimonials, ...testimonials].map((t, i) => (
                        <TestimonialCard key={i} data={t} />
                    ))}
                </motion.div>
            </div>
        </section>
    );
};

export default Testimonials;
