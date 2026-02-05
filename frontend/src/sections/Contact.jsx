import React from 'react';
import { motion } from 'framer-motion';

const Contact = () => {
    return (
        <section className="py-20 bg-zinc-950 text-white relative">
            <div className="container mx-auto px-4 max-w-4xl">
                <div className="bg-zinc-900/30 backdrop-blur-xl border border-white/10 rounded-3xl p-8 md:p-12 overflow-hidden relative">
                    {/* Background glow */}
                    <div className="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-[100px] -z-10" />

                    <h2 className="text-4xl font-bold mb-8 text-center">Entre em Contato</h2>

                    <form className="space-y-6 relative z-10">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="space-y-2">
                                <label className="text-sm font-medium text-zinc-400 ml-1">Nome</label>
                                <input type="text" className="w-full bg-zinc-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-colors" placeholder="Seu nome" />
                            </div>
                            <div className="space-y-2">
                                <label className="text-sm font-medium text-zinc-400 ml-1">Telefone</label>
                                <input type="text" className="w-full bg-zinc-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-colors" placeholder="(00) 00000-0000" />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-zinc-400 ml-1">Email</label>
                            <input type="email" className="w-full bg-zinc-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-colors" placeholder="seu@email.com" />
                        </div>
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-zinc-400 ml-1">Mensagem</label>
                            <textarea rows="4" className="w-full bg-zinc-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-colors" placeholder="Como podemos ajudar?" />
                        </div>

                        <motion.button
                            whileHover={{ scale: 1.02 }}
                            whileTap={{ scale: 0.98 }}
                            className="w-full py-4 bg-gradient-to-r from-emerald-500 to-emerald-700 rounded-xl font-bold text-lg uppercase tracking-wide shadow-lg shadow-emerald-500/20"
                        >
                            Enviar Mensagem
                        </motion.button>
                    </form>
                </div>

                <div className="mt-12 text-center text-zinc-500 text-sm">
                    <p>Rua Exemplo, 123 - Centro, Cidade - SP</p>
                    <p>(11) 99999-9999 | contato@purostylo.com</p>
                </div>
            </div>
        </section>
    );
};

export default Contact;
