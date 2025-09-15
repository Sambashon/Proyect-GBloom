-- A usuario le faltaría dos campos más mínimo:
-- admin y fecha de expiración de cuenta.
-- Esto es fabuloso para cuentas temporales que permiten a los usuarios jugar sin registrarse y eliminar cuentas sin usar
-- También faltaría agregar todo lo que serían estadisticas
create table usuario (
    username varchar(32) not null,
    correo varchar(320) not null,
    contraseña varchar(100) not null,
    fechaCreacion date not null, -- Agregado
    fechaNacimiento date not null,
    descripcion varchar(200),
    verificado boolean default false, -- Agregado
    admin boolean default false, -- Agregado
    primary key (username)
);

create table sesion (
    token varchar(64) not null,
    username varchar(32) not null,
    expira datetime not null,
    fecha datetime not null, -- Agregado
    mantener boolean default false, -- Agregado
    primary key (token),
    foreign key (username) references usuario(username)
);

create table dado (
    id varchar(9) not null,
    descripcion varchar(50),
    primary key (id)
);

create table dinosaurio (
    id varchar(9) not null,
    descripcion varchar(50),
    primary key (id)
);

create table partida (
    id int not null auto_increment,
    nombre varchar(32) not null, -- Agregado
    host varchar(32) not null, -- Agregado
    fecha datetime not null,
    horaInicio time,
    horaFinal time,
    cantidadJugadores int not null,
    modoJuego varchar(15) not null, -- Agregado (Modo de juego virual o seguimiento)
    turnoActual int,
    faseActual int,
    dadoId varchar(9),
    -- numJugador int, Eliminado
    primary key (id),
    foreign key (dadoId) references dado(id),
    foreign key (host) references usuario(username)
);

-- NUEVAS TABLAS

create table codigo_temporal ( -- Nueva Tabla!!!
    codigo varchar(64) not null,
    username varchar(32) not null,
    expira datetime not null,
    primary key (codigo),
    foreign key (username) references usuario(username)
);

create table lobby ( -- Nueva Tabla!!!
    codigo varchar(9) not null,
    partidaId int not null,
    cantidadJugadores int not null,
    comienza boolean default false,
    primary key (codigo),
    foreign key (partidaId) references partida(id)
);

create table conecta ( -- Nueva Tabla!!!
    codigo varchar(9) not null,
    username varchar(32) not null,
    primary key (codigo, username),
    foreign key (codigo) references lobby(codigo),
    foreign key (username) references usuario(username)
);

-- |||||||||||||

-- Revisar la sitaxis de las foreign keys
create table juega (
    username varchar(32) not null,
    partidaId int not null,
    puntos int,
    numJugador int,
    jugando boolean default false, -- Agregado
    estado varchar(20), -- Agregado
    primary key (username, partidaId),
    foreign key (username) references usuario(username),
    foreign key (partidaId) references partida(id)
);

create table inventario (
    username varchar(32) not null,
    partidaId int not null,
    dinosaurioId varchar(9) not null,
    cantidad int not null,
    primary key (username, partidaId, dinosaurioId),
    foreign key (username, partidaId) references juega(username, partidaId),
    foreign key (dinosaurioId) references dinosaurio(id)
);

create table tablero (
    username varchar(32) not null,
    partidaId int not null,
    dinosaurioId varchar(9) not null,
    recinto varchar(11),
    cantidad int not null,
    primary key (username, partidaId, dinosaurioId, recinto),
    foreign key (username, partidaId) references juega(username, partidaId),
    foreign key (dinosaurioId) references dinosaurio(id)
);

-- Nota: En el MR original había una clave externa de username, partidaId y dinosuarioId referenciando a inventario.
-- Aunque sería correcto, conceptualmente entra en un conflicto: No puedo tener un dinosaurio en el tablero si no está
-- registrado en el inventario. Esto al inicio suena coherente, la verdad es que el enfoque es que inventario y tablero,
-- dependen del usuario jugando una partida y luego las reestricciones se realizan en php