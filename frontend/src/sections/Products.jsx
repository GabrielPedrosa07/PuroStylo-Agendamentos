import React, { useRef } from 'react';
import { motion, useTransform, useScroll } from 'framer-motion';

const ProductCard = ({ product }) => {
    return (
        <motion.div
            whileHover={{ scale: 1.05 }}
            className="min-w-[300px] md:min-w-[400px] h-[500px] relative bg-zinc-900 rounded-xl overflow-hidden group border border-zinc-800 flex-shrink-0"
        >
            <div className="w-full h-full relative">
                <img
                    src={product.img}
                    alt={product.name}
                    className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    onError={(e) => {
                        e.target.src = "/img/produtos/sem-foto.jpg"; // Fallback
                    }}
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent flex flex-col justify-end p-8">
                    <h3 className="text-3xl font-black text-white mb-2 italic uppercase drop-shadow-md">{product.name}</h3>
                    <p className="text-cyan-400 font-mono text-xl mb-4 font-bold">{product.price}</p>
                    <button className="w-full py-3 bg-white text-black font-black uppercase tracking-widest hover:bg-pink-500 hover:text-white transition-colors">
                        Adicionar
                    </button>
                </div>
            </div>
        </motion.div>
    )
}

const Products = () => {
    const targetRef = useRef(null);
    const { scrollYProgress } = useScroll({
        target: targetRef,
    });

    const x = useTransform(scrollYProgress, [0, 1], ["0%", "-50%"]);

    const products = [
        { id: 1, name: "Esmalte Premium", price: "R$ 19,90", img: "/img/produtos/14-06-2022-16-47-12-esmalte.png" },
        { id: 2, name: "Kit Manicure", price: "R$ 150,00", img: "/img/produtos/14-06-2022-17-32-16-MANICURE-04.png" },
        { id: 3, name: "Camisa Xamã", price: "R$ 89,90", img: "/img/produtos/28-12-2025-21-54-34-XAMA-RUNNING-CAMISA-FUNDO-TRANSPARENTE.png" },
        { id: 4, name: "Upload Client", price: "R$ 70,00", img: "/img/produtos/07-12-2025-00-26-33-assets_client_upload_media_f84f64296f9512a9743260c2629a2a7a778d328b_media_01jzchyey5fkbrgs63exz3hgmn.png" },
        // Duplicating for scroll length
        { id: 5, name: "Esmalte Premium", price: "R$ 19,90", img: "/img/produtos/14-06-2022-16-47-12-esmalte.png" },
        { id: 6, name: "Barba Viking", price: "R$ 35,00", img: "/img/servicos/14-06-2022-15-39-39-BARBA-01.png" }, // Reusing correct image
    ];

    return (
        <section ref={targetRef} className="relative h-[250vh] bg-[#050505]">
            <div className="sticky top-0 h-screen flex flex-col justify-center overflow-hidden">
                <div className="absolute top-10 left-10 z-10 mix-blend-difference pointer-events-none">
                    <h2 className="text-6xl md:text-8xl font-black text-white px-4 border-l-8 border-pink-500 uppercase tracking-tighter italic drop-shadow-[0_0_15px_rgba(236,72,153,0.8)]">
                        LOJA
                        <span className="block text-2xl md:text-3xl font-bold text-cyan-400 tracking-widest mt-2 not-italic">
                            EXCLUSIVE
                        </span>
                    </h2>
                </div>

                {/* Background Strip */}
                <div className="absolute top-1/2 left-0 w-full h-96 bg-gradient-to-r from-pink-900/30 via-purple-900/30 to-cyan-900/30 -skew-y-3 blur-3xl" />

                <div className="relative w-full overflow-hidden">
                    <motion.div style={{ x }} className="flex gap-10 pl-[10vw]">
                        {products.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </motion.div>
                </div>
            </div>
        </section>
    );
};

export default Products;
