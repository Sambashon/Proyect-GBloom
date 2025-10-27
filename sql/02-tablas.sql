
create table usuario (
    username varchar(32) not null,
    correo varchar(320) not null,
    contraseña varchar(100) not null,
    fechaCreacion date not null, 
    fechaNacimiento date not null,
    descripcion varchar(200),
    verificado boolean default false, 
    victorias int default 0,
    jugadas int default 0,
    admin boolean default false, 
    primary key (username)
);

create table sesion (
    token varchar(64) not null,
    username varchar(32) not null,
    expira datetime not null,
    fecha datetime not null, 
    mantener boolean default false, 
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
    nombre varchar(32) not null, 
    host varchar(32) not null, 
    fecha datetime not null,
    horaInicio time,
    horaFinal time,
    cantidadJugadores int not null,
    modoJuego varchar(15) not null, 
    turnoActual int,
    faseActual int,
    dadoId varchar(9),
    primary key (id),
    foreign key (dadoId) references dado(id),
    foreign key (host) references usuario(username)
);

create table codigo_temporal ( 
    codigo varchar(64) not null,
    username varchar(32) not null,
    expira datetime not null,
    primary key (codigo),
    foreign key (username) references usuario(username)
);

create table lobby (
    codigo varchar(9) not null,
    partidaId int not null,
    cantidadJugadores int not null,
    comienza boolean default false,
    primary key (codigo),
    foreign key (partidaId) references partida(id)
);

create table conecta (
    codigo varchar(9) not null,
    username varchar(32) not null,
    primary key (codigo, username),
    foreign key (codigo) references lobby(codigo),
    foreign key (username) references usuario(username)
);

create table juega (
    username varchar(32) not null,
    partidaId int not null,
    puntos int,
    numJugador int,
    jugando boolean default false,
    estado varchar(20),
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