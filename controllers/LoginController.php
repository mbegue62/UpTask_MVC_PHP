<?php
namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

 class LoginController {

    public static function login(Router $router) {
       $alertas = [];
        if($_SERVER['REQUEST_METHOD']=== 'POST') {
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();
            if(empty($alertas)) {
            // Verificar que el usuario exista
            $usuario = Usuario::where('email', $usuario->email);

            if(!$usuario || !$usuario->confirmado) {
                Usuario::setAlerta('error', 'El Usuario no Existe o no esta Confirmado');
            } else {
                // El usuario existe
                if(password_verify($_POST['password'], $usuario->password)) {
                    // Iniciar sesión
                    session_start();
                    $_SESSION['id'] =$usuario->id;
                    $_SESSION['nombre'] =$usuario->nombre;
                    $_SESSION['email'] =$usuario->email;
                    $_SESSION['login'] =TRUE;
                   // Reedireccionar

                   header('Location: /dashboard');
                } else {
                    Usuario::setAlerta('error', 'El Password es Incorrecto');;
                }
            }

            }
        }
        // Render a la vista
        $alertas = Usuario::getAlertas();
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout(Router $router) {
        session_start();
        $_SESSION =[];
        header('Location: /');       
       
    }

    public static function crear(Router $router) {
        $alertas =[];
        $usuario = new Usuario;
        
        if($_SERVER['REQUEST_METHOD']=== 'POST') {
            $usuario->sincronizar($_POST);
            
            $alertas = $usuario->ValidacionCuentaNueva();

            $existeUsuario = Usuario::where('email', $usuario->email);
            if(empty($alertas)) {
                if($existeUsuario) {
                    Usuario::setAlerta('error', 'El Usuario ya esta Registrado');
                    $alertas = Usuario::getAlertas();
                   } else {
                    // hashear el password
                    $usuario->hashPassword();
                    
                    //Eliminar el password2
                    unset($usuario->password2);
                    
                    // Generar el token
                    $usuario->crearToken();
                    
                    // Crear un nuevo usuario
                    $resultado = $usuario->guardar();

                    // Enviar Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();
                    if($resultado) {
                        header('Location: /mensaje');
                    }
                   }
            }
            
        }

        $router->render('auth/crear', [
           'titulo' => 'Crea tu cuenta en UpTask',
           'usuario' => $usuario,
           'alertas' =>$alertas
        ]);
    }

    public static function olvide(Router $router) {
        $alertas =[];
        if($_SERVER['REQUEST_METHOD']=== 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarMail();

            if(empty($alertas)) {
                // Buscar el Usuario
                $usuario = Usuario::where('email', $usuario->email);

                if($usuario && $usuario->confirmado) {
                    // Generar un nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    //Actualizar el Usuario
                    $usuario->guardar();

                    //Enviar mail
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();
                    //Imprimir la alerta
                    Usuario::setAlerta('exito', 'Revisa tu E-mail');
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe, o no esta confirmdo');
                    
                }
                
            }
        }
        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide', [
            'titulo' => 'Recupera tu Password',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {
        $token =s($_GET['token']);
        $mostrar = TRUE;
        
        if(!$token) header('Location: /');

        // Encontrar al ususario con este token
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Usuario no Válido');
            $mostrar = FALSE;
        } 
       
        if($_SERVER['REQUEST_METHOD']=== 'POST') {

            $usuario->sincronizar($_POST);

            // Validar el Password
            $alertas = $usuario->validarPassword();

            
            if(empty($alertas)) {

                // hashear el nuevo password
                $usuario->hashPassword();
                // Elimar el token
                $usuario->token=NULL;
                // Gaurdar el usuario en DB
                $resultado = $usuario->guardar();
                
                // Redireccionar a la página principal
                if($resultado) {
                    header('Location: /');
                }
            }
           
            
            
              
        }
        $alertas = Usuario::getAlertas();
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Password',
            'alertas'=> $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta Creada Exitosamente'
        ]);
    }

    public static function confirmar(Router $router) {
       
        $token =s($_GET['token']);
        if(!$token) header('Location: /');

        // Encontrar al ususario con este token
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Usuario no Válido');
        } else {
            // confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = NULL;
            unset($usuario->password2);
            // Guardar en la BD

            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');
        }
        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu Cuenta UpTask',
            'alertas'=>$alertas
        ]);
    }
 }