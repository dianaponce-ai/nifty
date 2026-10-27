<?php
/**
 * Contenido inicial: casos de éxito y reseñas.
 * Se carga una sola vez en la instalación. Después todo se edita desde /admin.
 */
function seed_content(): void {
    if ((int) val('SELECT COUNT(*) FROM cases') === 0) {
        foreach (seed_cases() as $i => $c) {
            $row = [
                'slug' => $c['slug'], 'client' => $c['client'], 'industry' => $c['industry'], 'location' => $c['location'], 'scope' => $c['scope'],
                'title' => $c['title'], 'subtitle' => $c['subtitle'], 'card_summary' => $c['card'], 'facts' => $c['facts'],
                'challenge_title' => $c['challenge_title'], 'challenge_intro' => $c['challenge_intro'], 'challenges' => jenc($c['challenges']),
                'work_title' => $c['work_title'], 'work_intro' => $c['work_intro'],
                'services_ops' => jenc($c['ops']), 'services_mk' => jenc($c['mk']), 'stats' => jenc($c['stats']),
                'highlight' => jenc($c['highlight']), 'results_title' => $c['results_title'], 'results' => jenc($c['results']),
                'hero_image' => $c['hero'] ?? '', 'highlight_image' => $c['hl_image'] ?? '', 'gallery' => jenc($c['gallery'] ?? []),
                'site_url' => $c['site'], 'published' => 1, 'sort' => ($i + 1) * 10, 'created_at' => now(), 'updated_at' => now(),
            ];
            insert('cases', $row);
        }
    }
    if ((int) val('SELECT COUNT(*) FROM reviews') === 0) {
        $reviews = [
            ['Jose Gabriel Jerez', 'Nifty ha sido clave para el crecimiento de las ventas en mi negocio. No solo son un partner estratégico, sino que son parte del equipo 💪🙌'],
            ['Mariam Navarro', 'Recomiendo ciegamente a Nifty, su trabajo y dedicación son increíbles. No solo te abren la mente con distintas perspectivas, sino que también te muestran…'],
            ['Julieta Paleari', 'Tuve una excelente experiencia trabajando con Nifty Agency. Desde el primer contacto, su equipo mostró un alto nivel de profesionalismo y dedicación…'],
            ['Gabriela María Silva', 'Excelente organización y espíritu creativo, súper recomendable. Todo un equipo de trabajo con las mejores ideas para escalar tu marca y negocio.'],
            ['Eliend Martínez', 'Hace un año trabajé con el equipo de Nifty para la creación de mi primera página web de mi salón. Súper profesionales y atentos a cada detalle…'],
        ];
        foreach ($reviews as $i => [$a, $t]) insert('reviews', ['author' => $a, 'text' => $t, 'rating' => 5, 'source' => 'Google', 'featured' => 1, 'sort' => $i, 'created_at' => now()]);
    }
}

function seed_cases(): array {
    return [
        [
            'slug' => 'eunoia-campus', 'client' => 'Eunoia Campus', 'industry' => 'Educación', 'location' => 'Puerto Retiro, CABA', 'scope' => 'Ecosistema completo',
            'title' => 'Una escuela de marketing construida de punta a punta.',
            'subtitle' => 'Desarrollamos la operación completa de Eunoia Campus: el sistema administrativo, la gestión de alumnos, el sitio web, la marca, la pauta y el contenido. Hoy es una de las escuelas de marketing con mejor posicionamiento del mercado.',
            'card' => 'Sistema administrativo, LMS, web, marca, pauta y una red de ~30 creadores UGC para posicionar una escuela de marketing.',
            'facts' => 'Nifty desarrolló el sistema administrativo, el sistema de gestión de alumnos, la web completa, el branding, la pauta, el contenido estratégico y el UGC (estrategia y ejecución con ~30 creadores), que ayudó a posicionar a Eunoia en el mercado de escuelas de marketing.',
            'challenge_title' => 'Entrar a un mercado con escuelas instaladas hace años.',
            'challenge_intro' => 'Eunoia Campus necesitaba ganarse un lugar en el mercado de formación en marketing digital. Para lograrlo tenía que resolver tres frentes al mismo tiempo, con un solo equipo y una sola visión de marca.',
            'challenges' => [
                ['t' => 'Operar con orden desde el día uno', 'd' => 'Inscripciones, cohortes, pagos, aula, evaluaciones y certificados funcionando en un mismo sistema.'],
                ['t' => 'Construir una marca con credibilidad', 'd' => 'Una identidad propia, coherente en la web, las redes y el aula, capaz de competir con escuelas con más trayectoria.'],
                ['t' => 'Generar demanda sostenida', 'd' => 'Un flujo constante de interesados calificados para cada curso y cada nueva cohorte.'],
            ],
            'work_title' => 'Siete servicios, un mismo ecosistema.',
            'work_intro' => 'Trabajamos la escuela como un sistema integrado: la tecnología sostiene la operación y la marca genera la demanda. Cada pieza alimenta a la siguiente.',
            'ops' => [
                ['t' => 'Sistema administrativo', 'd' => 'Gestión interna de inscripciones, cohortes y seguimiento comercial de cada curso.'],
                ['t' => 'Sistema de gestión de alumnos', 'd' => 'Acceso por cohorte, aula virtual, evaluaciones propias con corrección automática y certificados de cada programa.'],
                ['t' => 'Sitio web completo', 'd' => 'Diseño y desarrollo del sitio, landings por curso, formularios conectados al CRM y estructura optimizada para buscadores.'],
            ],
            'mk' => [
                ['t' => 'Branding', 'd' => 'Identidad visual, tono de comunicación y sistema gráfico aplicado a todos los puntos de contacto.'],
                ['t' => 'Pauta publicitaria', 'd' => 'Campañas de performance para captar leads calificados en cada lanzamiento de cohorte.'],
                ['t' => 'Contenido estratégico', 'd' => 'Pilares de contenido pensados para educar, generar autoridad y acompañar la decisión de compra.'],
                ['t' => 'Contenido UGC', 'd' => 'Estrategia y ejecución de una red de cerca de 30 creadores que hablaron de Eunoia con su propia voz.'],
            ],
            'stats' => [
                ['value' => '7', 'label' => 'servicios integrados en un mismo ecosistema'],
                ['value' => '~30', 'label' => 'creadores UGC coordinados'],
                ['value' => '360°', 'label' => 'operación, marca y demanda bajo un equipo'],
            ],
            'highlight' => ['kicker' => 'Contenido UGC', 'title' => 'Cerca de 30 voces contando qué es Eunoia.',
                'text' => 'El contenido generado por usuarios fue clave para el posicionamiento. Diseñamos la estrategia y coordinamos la ejecución con aproximadamente 30 personas que generaron contenido estratégico para la marca.',
                'steps' => [
                    ['t' => 'Estrategia', 'd' => 'Definimos mensajes, formatos y objetivos de cada pieza según la etapa del alumno.'],
                    ['t' => 'Casting y briefing', 'd' => 'Seleccionamos a cada creador y le dimos una guía clara para mantener la coherencia de marca.'],
                    ['t' => 'Ejecución y distribución', 'd' => 'Coordinamos la producción y usamos el contenido en redes orgánicas y en la pauta.'],
                ]],
            'results_title' => 'Eunoia Campus se posicionó como una escuela de marketing de referencia.',
            'results' => [
                ['t' => 'Una operación ordenada', 'd' => 'La escuela gestiona alumnos, cohortes, evaluaciones y certificados desde sistemas hechos a su medida.'],
                ['t' => 'Una marca reconocida', 'd' => 'Identidad, contenido estratégico y UGC trabajando juntos le dieron una voz propia en el mercado.'],
                ['t' => 'Demanda en crecimiento', 'd' => 'Pauta, web y reputación conectadas generan interesados calificados en cada nueva cohorte.'],
            ],
            'hero' => 'https://nifty-agency.com/wp-content/uploads/2026/10/Screenshot-2026-10-07-at-8.51.45-PM-scaled.png',
            'hl_image' => 'https://nifty-agency.com/wp-content/uploads/2026/10/Screenshot-2026-10-07-at-8.53.54-PM.png',
            'gallery' => [['src' => 'https://nifty-agency.com/wp-content/uploads/2026/10/Screenshot-2026-10-07-at-8.54.44-PM.png', 'caption' => 'Reseñas de alumnos en Google']],
            'site' => 'https://eunoiacampus.com/',
        ],
        [
            'slug' => 'dar-grieco', 'client' => 'Dar Grieco', 'industry' => 'Industria · Distribución B2B', 'location' => 'Pompeya, CABA', 'scope' => 'Sistema de ventas + pauta',
            'title' => 'Un sistema de ventas B2B para más de 20.000 productos, integrado con su ERP.',
            'subtitle' => 'Dar Grieco es un mayorista de rodamientos y componentes de transmisión. Desarrollamos su sistema de gestión de ventas online integrado con Netegia, con precios por marca y por cliente, cuenta corriente y app móvil, y lo alimentamos con pauta B2B en Google.',
            'card' => 'Sistema de gestión de ventas B2B integrado con Netegia, para un inventario de más de 20.000 productos, más pauta en Google Ads.',
            'facts' => 'Mayorista B2B de rodamientos. Sistema de gestión de ventas a medida integrado con el ERP Netegia, inventario de más de 20.000 productos, descuentos por marca y por cliente, equivalencias, stock en tránsito, cuenta corriente, calculadora de dólar y app PWA. Pauta en Google Ads segmentada en segmento industrial y automotriz. Cerca del 50% del tráfico del sitio llega por la pauta. Operando desde octubre de 2025.',
            'challenge_title' => 'Vender online un catálogo industrial enorme, con la lógica comercial de un mayorista.',
            'challenge_intro' => 'Un catálogo de miles de códigos, precios que cambian según la marca y el cliente, y un equipo comercial acostumbrado al teléfono. El desafío era llevar todo eso a un canal digital sin perder la forma de vender de la empresa.',
            'challenges' => [
                ['t' => 'Un inventario de más de 20.000 productos', 'd' => 'Rodamientos, rulemanes, embragues, poleas y componentes de transmisión de decenas de marcas, con equivalencias entre códigos.'],
                ['t' => 'Precios a medida de cada cliente', 'd' => 'Descuentos por marca y por cliente, IVA, cotización del dólar y stock en tránsito, como en el mostrador.'],
                ['t' => 'Integración con el ERP', 'd' => 'Cada pedido web tenía que llegar a Netegia para seguir el circuito comercial y de depósito.'],
            ],
            'work_title' => 'Tecnología que vende y pauta que trae compradores.',
            'work_intro' => 'Construimos un sistema de ventas hecho para comprar como en el mostrador, pero las 24 horas, y lo conectamos con campañas que llegan a quienes compran rodamientos para su industria.',
            'ops' => [
                ['t' => 'Sistema de gestión de ventas B2B', 'd' => 'Tienda tipo tabla con filtros por marca y stock, precios y descuentos por cliente, equivalencias, ofertas y calculadora de dólar.'],
                ['t' => 'Integración con Netegia', 'd' => 'Los pedidos web viajan al ERP y desde allí siguen el proceso comercial y de depósito.'],
                ['t' => 'Panel del cliente y app móvil', 'd' => 'Cuenta corriente, historial y vendedor asignado, más una app progresiva (PWA) para comprar desde el celular.'],
            ],
            'mk' => [
                ['t' => 'Google Ads B2B', 'd' => 'Campañas de búsqueda segmentadas en los sectores industrial y automotriz, con foco en marcas estratégicas.'],
                ['t' => 'SEO + GEO', 'd' => 'Posicionamiento en buscadores y en motores de IA para las búsquedas de producto y marca.'],
            ],
            'stats' => [
                ['value' => '+20.000', 'label' => 'productos gestionados en el sistema'],
                ['value' => 'Netegia', 'label' => 'ERP integrado con la tienda'],
                ['value' => '~50%', 'label' => 'del tráfico del sitio llega por la pauta'],
                ['value' => '24/7', 'label' => 'autoservicio para clientes mayoristas'],
            ],
            'highlight' => ['kicker' => 'El sistema', 'title' => 'Comprar como en el mostrador, pero a cualquier hora.',
                'text' => 'El cliente entra con su usuario, ve sus precios y su cuenta corriente, encuentra el código que necesita en segundos y confirma el pedido, que llega directo al ERP.',
                'steps' => [
                    ['t' => 'Encontrar', 'd' => 'Tabla de productos con filtros por marca, línea y stock disponible.'],
                    ['t' => 'Cotizar', 'd' => 'Precios y descuentos propios de cada cliente, en la moneda que corresponde.'],
                    ['t' => 'Pedir', 'd' => 'El pedido se registra y se envía a Netegia para su preparación.'],
                ]],
            'results_title' => 'Dar Grieco sumó un canal de venta digital que trabaja todo el día.',
            'results' => [
                ['t' => 'Autoservicio mayorista', 'd' => 'Los clientes consultan precios, stock y cuenta corriente y hacen sus pedidos sin depender del teléfono.'],
                ['t' => 'Operación conectada', 'd' => 'La tienda y el ERP hablan entre sí, con la lógica comercial de siempre.'],
                ['t' => 'Demanda B2B medible', 'd' => 'La pauta en Google trae compradores industriales y ya explica cerca de la mitad del tráfico del sitio.'],
            ],
            'site' => 'https://dar-grieco.com/',
        ],
        [
            'slug' => 'thermomix', 'client' => 'Thermomix', 'industry' => 'Electrodomésticos · Venta directa', 'location' => 'Argentina y Uruguay', 'scope' => 'Ecosistema de venta digital',
            'title' => 'Un ecosistema de venta digital que vendió más de 100 equipos durante más de 3 años.',
            'subtitle' => 'Acompañamos a Thermomix en su llegada a Argentina con pauta publicitaria, contenido, sitio web y un sistema de ventas digitales con contact center propio dedicado a cerrar cada venta.',
            'card' => 'Pauta, contenido, sitio web y sistema de ventas digitales: más de 100 equipos vendidos durante más de 3 años.',
            'facts' => 'Thermomix lanzaba su marca en Argentina. Gestionamos pauta en Meta y Google, contenido, sitio web con CRO continuo y un sistema de ventas digitales con contact center dedicado. Vendimos más de 100 equipos durante más de 3 años. Récord de 150 ventas en un mes entre Argentina y Uruguay; fuimos el equipo de ventas más exitoso de la marca, superando a su mejor vendedor individual. Trabajo realizado bajo el nombre anterior de la agencia, Zougud Performance.',
            'challenge_title' => 'Vender online un producto premium que tradicionalmente se vende en demostraciones.',
            'challenge_intro' => 'Thermomix se apoya históricamente en la venta presencial. Al llegar a Argentina necesitaba un canal digital capaz de generar interés, educar sobre el producto y cerrar ventas de alto valor a distancia.',
            'challenges' => [
                ['t' => 'Un ticket alto', 'd' => 'Una decisión de compra importante, que necesita confianza y acompañamiento.'],
                ['t' => 'Un producto que hay que mostrar', 'd' => 'Lo que en una demostración se ve en minutos, online hay que contarlo con contenido.'],
                ['t' => 'Cerrar la venta a distancia', 'd' => 'Convertir el interés generado por la pauta en ventas concretas, todos los meses.'],
            ],
            'work_title' => 'De la pauta al cierre, en un solo sistema.',
            'work_intro' => 'Diseñamos un circuito completo: la pauta y el contenido generan interés, el sitio convierte y el contact center acompaña hasta la venta.',
            'ops' => [
                ['t' => 'Sitio web con CRO continuo', 'd' => 'Sitio orientado a la conversión, optimizado mes a mes con datos reales.'],
                ['t' => 'Sistema de ventas digitales', 'd' => 'Circuito de leads, seguimiento y cierre con un contact center dedicado a la venta online.'],
            ],
            'mk' => [
                ['t' => 'Pauta publicitaria', 'd' => 'Campañas en Meta y Google para generar leads calificados.'],
                ['t' => 'Contenido', 'd' => 'Piezas que muestran el producto en uso y responden las dudas antes de la compra.'],
            ],
            'stats' => [
                ['value' => '+100', 'label' => 'equipos vendidos'],
                ['value' => '+3 años', 'label' => 'de gestión continua'],
                ['value' => '150', 'label' => 'ventas en un solo mes entre Argentina y Uruguay'],
                ['value' => '#1', 'label' => 'equipo de ventas de la marca'],
            ],
            'highlight' => ['kicker' => 'Contact center', 'title' => 'El equipo de ventas más exitoso de la marca.',
                'text' => 'El contact center dedicado convirtió el interés generado por la pauta en ventas: en su mejor mes cerró 150 ventas entre Argentina y Uruguay, superando al mejor vendedor individual de Thermomix.',
                'steps' => [
                    ['t' => 'Atraer', 'd' => 'Pauta y contenido que generan leads con intención real.'],
                    ['t' => 'Acompañar', 'd' => 'Asesoramiento personalizado para resolver dudas y objeciones.'],
                    ['t' => 'Cerrar', 'd' => 'Seguimiento disciplinado hasta concretar la compra online.'],
                ]],
            'results_title' => 'Un canal digital que vendió de forma sostenida durante años.',
            'results' => [
                ['t' => 'Ventas sostenidas', 'd' => 'Más de 100 equipos vendidos y más de 3 años de gestión continua.'],
                ['t' => 'Un récord de ventas', 'd' => '150 ventas en un mes entre Argentina y Uruguay.'],
                ['t' => 'Ecosistema end-to-end', 'd' => 'Pauta, contenido, sitio y contact center trabajando como un solo equipo.'],
            ],
            'site' => '',
        ],
        [
            'slug' => 'aco-workspace', 'client' => 'ACO Workspace', 'industry' => 'Coworking', 'location' => 'Buenos Aires', 'scope' => 'Marca, contenido, pauta y web',
            'title' => 'Una marca de coworking con identidad propia, contenido y demanda.',
            'subtitle' => 'Ayudamos a ACO Workspace, un coworking con dos sedes en Buenos Aires, a desarrollar su marca con pauta publicitaria, una estrategia de contenidos y un sitio web pensado para convertir visitas en consultas.',
            'card' => 'Desarrollo de marca con pauta publicitaria, estrategia de contenidos y sitio web para un coworking con dos sedes.',
            'facts' => 'Coworking con dos sedes en Buenos Aires. Desarrollamos la marca con pauta publicitaria, contenidos (estrategia de contenido y posicionamiento a seis meses) y sitio web.',
            'challenge_title' => 'Diferenciarse en un mercado de espacios que parecen todos iguales.',
            'challenge_intro' => 'En el mundo del coworking, las fotos de escritorios y salas se parecen mucho. ACO necesitaba una marca reconocible y un sistema para atraer consultas para sus dos sedes.',
            'challenges' => [
                ['t' => 'Una marca con personalidad', 'd' => 'Comunicar algo más que metros cuadrados: comunidad, servicio y forma de trabajar.'],
                ['t' => 'Dos sedes, una sola marca', 'd' => 'Mensajes y campañas que funcionen para cada ubicación sin perder coherencia.'],
                ['t' => 'Consultas constantes', 'd' => 'Un flujo sostenido de interesados en puestos, oficinas y salas.'],
            ],
            'work_title' => 'Marca, contenido, pauta y web trabajando juntos.',
            'work_intro' => 'Construimos la marca y la pusimos en movimiento: un sitio que convierte, contenido que posiciona y pauta que llena la agenda de visitas.',
            'ops' => [
                ['t' => 'Sitio web', 'd' => 'Sitio con la propuesta de cada sede, espacios y formularios de contacto conectados.'],
            ],
            'mk' => [
                ['t' => 'Desarrollo de marca', 'd' => 'Tono, mensajes y criterios visuales para una comunicación coherente.'],
                ['t' => 'Estrategia de contenidos', 'd' => 'Plan de contenido y posicionamiento a seis meses.'],
                ['t' => 'Pauta publicitaria', 'd' => 'Campañas para generar consultas y visitas a cada sede.'],
            ],
            'stats' => [
                ['value' => '2', 'label' => 'sedes en Buenos Aires'],
                ['value' => '6 meses', 'label' => 'de estrategia de contenido y posicionamiento'],
                ['value' => '3', 'label' => 'frentes integrados: web, contenido y pauta'],
            ],
            'highlight' => ['kicker' => 'Contenido', 'title' => 'Contar la experiencia, no solo el espacio.',
                'text' => 'La estrategia de contenidos puso el foco en cómo se trabaja en ACO: la comunidad, el servicio y el día a día de quienes eligen el espacio.',
                'steps' => [
                    ['t' => 'Pilares', 'd' => 'Definimos los temas que posicionan a la marca.'],
                    ['t' => 'Calendario', 'd' => 'Planificamos seis meses de publicaciones.'],
                    ['t' => 'Amplificación', 'd' => 'Usamos las mejores piezas en la pauta.'],
                ]],
            'results_title' => 'ACO Workspace comunica una marca propia y genera demanda para sus sedes.',
            'results' => [
                ['t' => 'Identidad clara', 'd' => 'Una marca coherente en el sitio, las redes y los anuncios.'],
                ['t' => 'Contenido con dirección', 'd' => 'Un plan de contenido que construye posicionamiento mes a mes.'],
                ['t' => 'Demanda activa', 'd' => 'Pauta y web conectadas para generar consultas para cada sede.'],
            ],
            'site' => 'https://acoworkspace.com/',
        ],
        [
            'slug' => 'btm', 'client' => 'BTM', 'industry' => 'Movilidad eléctrica', 'location' => 'Palermo y Olivos, Buenos Aires', 'scope' => 'Pauta + contact center',
            'title' => 'Pauta y contact center para vender movilidad eléctrica por WhatsApp.',
            'subtitle' => 'BTM vende y da servicio técnico a monopatines y bicicletas eléctricas en dos sedes. Gestionamos su pauta publicitaria y su contact center por WhatsApp, y rediseñamos el sitio para que comprar sea tan simple como mandar un mensaje.',
            'card' => 'Pauta publicitaria y gestión de contact center por WhatsApp para un negocio de movilidad eléctrica con dos sedes.',
            'facts' => 'Venta y servicio técnico de vehículos eléctricos. Gestionamos la pauta y el contact center: capacitación del equipo, etiquetas de WhatsApp Business, bot gestionado por Nifty, protocolo de servicio técnico, diseño de la integración Odoo + WhatsApp con 4 equipos CRM y 2 pipelines, ruteo de leads por sede. Rediseño de home, ficha y catálogo, compra directa por WhatsApp, calculadora de cuotas.',
            'challenge_title' => 'Muchas consultas, dos sedes y un canal que se desbordaba.',
            'challenge_intro' => 'La pauta generaba conversaciones, pero ventas y servicio técnico compartían el mismo canal. Hacía falta ordenar la atención para que ninguna consulta se perdiera.',
            'challenges' => [
                ['t' => 'Ventas y servicio técnico mezclados', 'd' => 'Consultas de compra y de reparación llegando por el mismo WhatsApp.'],
                ['t' => 'Dos sedes', 'd' => 'Cada lead tenía que llegar al equipo de la sede correcta.'],
                ['t' => 'Una compra con fricción', 'd' => 'El paso por carrito y checkout frenaba ventas que se cierran conversando.'],
            ],
            'work_title' => 'De la pauta a la conversación, y de la conversación a la venta.',
            'work_intro' => 'Unimos la generación de demanda con la atención: campañas que llevan a WhatsApp, un equipo preparado para responder y un sitio pensado para comprar conversando.',
            'ops' => [
                ['t' => 'Contact center por WhatsApp', 'd' => 'Capacitación del equipo, bot gestionado por Nifty, etiquetas por urgencia y estado y un protocolo de atención para servicio técnico.'],
                ['t' => 'Integración Odoo + WhatsApp', 'd' => 'Arquitectura de 4 equipos CRM (ventas y service por sede) y 2 pipelines, con ruteo automático por sede.'],
                ['t' => 'Rediseño del sitio', 'd' => 'Home, catálogo y ficha de producto rehechos a código, compra directa por WhatsApp y calculadora de cuotas.'],
            ],
            'mk' => [
                ['t' => 'Pauta publicitaria', 'd' => 'Campañas que generan conversaciones de venta, clasificadas por sede según palabra clave y UTM.'],
            ],
            'stats' => [
                ['value' => '2', 'label' => 'sedes atendidas con ruteo automático'],
                ['value' => '4', 'label' => 'equipos CRM diseñados para ventas y service'],
                ['value' => '1 clic', 'label' => 'del producto a la conversación por WhatsApp'],
            ],
            'highlight' => ['kicker' => 'Contact center', 'title' => 'Un canal ordenado para vender y para dar servicio.',
                'text' => 'Capacitamos al equipo y ordenamos la operación para que cada consulta tenga un responsable y un tiempo de respuesta.',
                'steps' => [
                    ['t' => 'Clasificar', 'd' => 'Etiquetas por urgencia, estado y tipo de consulta.'],
                    ['t' => 'Derivar', 'd' => 'Cada lead a la sede y al equipo que corresponde.'],
                    ['t' => 'Cumplir', 'd' => 'Un protocolo claro para mantener informado al cliente de servicio técnico.'],
                ]],
            'results_title' => 'BTM convierte la demanda de la pauta en ventas conversadas.',
            'results' => [
                ['t' => 'Atención ordenada', 'd' => 'Ventas y servicio técnico con circuitos y responsables claros.'],
                ['t' => 'Menos fricción', 'd' => 'Del anuncio al producto y del producto a WhatsApp, sin pasos de más.'],
                ['t' => 'Base para escalar', 'd' => 'Una arquitectura lista para integrar todo el canal con Odoo.'],
            ],
            'site' => 'https://btm.com.ar/',
        ],
        [
            'slug' => 'cmg-hair-studio', 'client' => 'CMG Hair Studio', 'industry' => 'Peluquería · Belleza', 'location' => 'Buenos Aires', 'scope' => 'Pauta + UGC',
            'title' => 'Más turnos que nunca y un posicionamiento como expertos en color.',
            'subtitle' => 'Con pauta publicitaria y gestión de contenido UGC, CMG Hair Studio llena su agenda como nunca antes y se está posicionando como un salón experto en color.',
            'card' => 'Pauta publicitaria y gestión de UGC: muchos más turnos que antes y posicionamiento como expertos en color.',
            'facts' => 'Peluquería. Con pauta publicitaria y gestión de UGC tiene muchos más turnos que antes y se está posicionando como expertos en color. También desarrollamos su sistema de gestión.',
            'challenge_title' => 'Destacarse en un rubro donde todos dicen hacer lo mismo.',
            'challenge_intro' => 'Hay muchas peluquerías y casi todas comunican igual. CMG quería llenar la agenda y, sobre todo, que la eligieran por su especialidad: el color.',
            'challenges' => [
                ['t' => 'Agenda con huecos', 'd' => 'Necesitaba un flujo constante de turnos, no picos ocasionales.'],
                ['t' => 'Una especialidad que mostrar', 'd' => 'El trabajo de color se entiende viéndolo, no leyéndolo.'],
                ['t' => 'Confianza', 'd' => 'Antes de un cambio de color, las clientas buscan pruebas reales.'],
            ],
            'work_title' => 'Contenido real y pauta que convierte en turnos.',
            'work_intro' => 'Usamos el contenido de clientas y estilistas como motor de la pauta: resultados reales, contados por quienes los viven.',
            'ops' => [
                ['t' => 'Sistema de gestión', 'd' => 'Turnos, clientas e información del salón en un sistema propio.'],
            ],
            'mk' => [
                ['t' => 'Pauta publicitaria', 'd' => 'Campañas orientadas a reservas de turnos, con foco en servicios de color.'],
                ['t' => 'Gestión de UGC', 'd' => 'Estrategia, coordinación y uso de contenido de clientas y del equipo.'],
            ],
            'stats' => [
                ['value' => 'Color', 'label' => 'la especialidad que hoy posiciona al salón'],
                ['value' => 'UGC', 'label' => 'contenido real como motor de la pauta'],
                ['value' => '+ turnos', 'label' => 'una agenda mucho más llena que antes'],
            ],
            'highlight' => ['kicker' => 'UGC', 'title' => 'Los resultados hablan por el salón.',
                'text' => 'El antes y después, el proceso y la experiencia contados por clientas reales convirtieron al color en la marca registrada de CMG.',
                'steps' => [
                    ['t' => 'Capturar', 'd' => 'Guías simples para registrar cada trabajo de color.'],
                    ['t' => 'Seleccionar', 'd' => 'Elegir las piezas que mejor muestran la especialidad.'],
                    ['t' => 'Amplificar', 'd' => 'Convertirlas en anuncios que llevan a reservar.'],
                ]],
            'results_title' => 'CMG Hair Studio tiene la agenda más llena de su historia.',
            'results' => [
                ['t' => 'Muchos más turnos', 'd' => 'La pauta con contenido real se traduce en reservas.'],
                ['t' => 'Expertos en color', 'd' => 'Un posicionamiento claro que la diferencia de la competencia.'],
                ['t' => 'Una comunidad que recomienda', 'd' => 'Clientas que muestran y cuentan su experiencia.'],
            ],
            'site' => '',
        ],
        [
            'slug' => 'dut-mora', 'client' => 'Dut Mora Peluquería', 'industry' => 'Peluquería · Belleza', 'location' => 'Buenos Aires', 'scope' => 'Pauta + UGC',
            'title' => 'Pauta y contenido real para una agenda que no para.',
            'subtitle' => 'Aplicamos en Dut Mora la misma fórmula que funciona en belleza: pauta publicitaria orientada a turnos y contenido UGC que muestra el trabajo del salón tal como es.',
            'card' => 'Pauta publicitaria orientada a turnos y gestión de contenido UGC para posicionar el trabajo del salón.',
            'facts' => 'Peluquería. Experiencia similar a CMG: pauta publicitaria y gestión de UGC para generar más turnos y posicionar la especialidad del salón.',
            'challenge_title' => 'Convertir seguidores en turnos.',
            'challenge_intro' => 'Dut Mora tenía un trabajo para mostrar y una comunidad que lo valoraba. Faltaba transformar esa atención en reservas constantes.',
            'challenges' => [
                ['t' => 'Reservas constantes', 'd' => 'Una agenda previsible, más allá de las temporadas altas.'],
                ['t' => 'Mostrar el trabajo', 'd' => 'Que cada servicio se vea con la calidad que tiene en el salón.'],
                ['t' => 'Diferenciarse', 'd' => 'Ser la opción elegida frente a otras peluquerías de la zona.'],
            ],
            'work_title' => 'La fórmula de belleza de Nifty.',
            'work_intro' => 'Pauta con objetivo de turnos, alimentada por contenido real del salón y de sus clientas.',
            'ops' => [],
            'mk' => [
                ['t' => 'Pauta publicitaria', 'd' => 'Campañas de mensajes y reservas segmentadas por zona.'],
                ['t' => 'Gestión de UGC', 'd' => 'Estrategia y coordinación de contenido real para redes y anuncios.'],
            ],
            'stats' => [
                ['value' => 'UGC', 'label' => 'contenido real del salón y sus clientas'],
                ['value' => '+ turnos', 'label' => 'reservas generadas desde la pauta'],
                ['value' => 'Local', 'label' => 'campañas segmentadas por zona'],
            ],
            'highlight' => ['kicker' => 'Método', 'title' => 'Del contenido al turno.',
                'text' => 'Cada pieza de contenido tiene un trabajo: mostrar un resultado y llevar a reservar.',
                'steps' => [
                    ['t' => 'Mostrar', 'd' => 'Contenido real de servicios y resultados.'],
                    ['t' => 'Invitar', 'd' => 'Anuncios con un llamado claro a reservar.'],
                    ['t' => 'Atender', 'd' => 'Respuesta rápida a cada consulta.'],
                ]],
            'results_title' => 'Dut Mora llena su agenda con pauta y contenido real.',
            'results' => [
                ['t' => 'Más reservas', 'd' => 'La pauta convierte la atención en turnos.'],
                ['t' => 'Una marca visible', 'd' => 'El trabajo del salón se ve en cada anuncio.'],
                ['t' => 'Una fórmula que escala', 'd' => 'El mismo método se ajusta mes a mes con datos.'],
            ],
            'site' => '',
        ],
    ];
}
