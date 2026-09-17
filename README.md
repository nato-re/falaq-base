# FalaQ - Projeto Laravel base para a 3ª Etapa

Rode os comandos a baixo no terminal na sua pasta de documentos para clonar e configurar o repositório
```sh
#git clone https://github.com/nato-re/falaq-base.git
#cd falaq-base
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```
