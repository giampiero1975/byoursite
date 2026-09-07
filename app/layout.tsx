import type { Metadata } from 'next';
import { Geist, Geist_Mono } from 'next/font/google';
import './globals.css';

const siteUrl = 'https://byoursite.com/';

const geistSans = Geist({
  variable: '--font-geist-sans',
  subsets: ['latin'],
});

const geistMono = Geist_Mono({
  variable: '--font-geist-mono',
  subsets: ['latin'],
});

export const metadata: Metadata = {
  metadataBase: new URL(siteUrl),
  title: 'BYOURSITE | Siti web, gestionali e API su misura',
  description:
    'BYOURSITE realizza siti vetrina, restyling, piccoli gestionali, automazioni, database e API REST su misura per professionisti, aziende e attività.',
  alternates: {
    canonical: siteUrl,
  },
  robots: {
    index: true,
    follow: true,
  },
  openGraph: {
    type: 'website',
    locale: 'it_IT',
    url: siteUrl,
    siteName: 'BYOURSITE',
    title: 'BYOURSITE | Il tuo sito, al tuo fianco',
    description:
      'Siti web, gestionali, automazioni e integrazioni API costruiti su misura: by your site, by your side.',
    images: [
      {
        url: '/og.png',
        alt: 'Logo BYOURSITE su fondo scuro',
      },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: 'BYOURSITE | Siti web, gestionali e API su misura',
    description:
      'Soluzioni web sartoriali: siti, gestionali, database, automazioni e API REST.',
    images: ['/og.png'],
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="it">
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased`}
      >
        {children}
      </body>
    </html>
  );
}

