# ─── Stage 1: Build React ─────────────────────────────────────────────────────
FROM node:20-alpine AS builder
WORKDIR /app

COPY package*.json ./
RUN npm ci --silent

COPY . .
RUN npm run build

# ─── Stage 2: Servir con Nginx ────────────────────────────────────────────────
FROM nginx:stable-alpine AS runtime

COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf.template

EXPOSE 80
# envsubst reemplaza ${BACKEND_URL} con la variable de entorno antes de arrancar nginx
CMD ["/bin/sh", "-c", "export NGINX_RESOLVER=$(awk '$1==\"nameserver\"{if($2~/^[0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+$/){print $2;exit}if(!v){v=\"[\"$2\"]\"}}END{if(v)print v}' /etc/resolv.conf); envsubst '${BACKEND_URL} ${NGINX_RESOLVER}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]
