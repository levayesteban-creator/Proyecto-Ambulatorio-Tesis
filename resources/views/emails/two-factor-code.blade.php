<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f8;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f8;padding:20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    <!-- HEADER -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:20px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:22px;font-weight:bold;">Consultorio Popular Tipo III</h1>
                            <h2 style="color:#f0f0f0;margin:5px 0 0;font-size:16px;">"El Chaparro de Guanta"</h2>
                        </td>
                    </tr>

                    <!-- CONTENIDO -->
                    <tr>
                        <td style="padding:30px;line-height:1.6;color:#333333;">
                            <h2 style="color:#666;font-size:20px;margin:0 0 15px;">Verificación en Dos Pasos</h2>
                            <p style="font-size:15px;color:#555;margin:0 0 10px;">
                                Hola {{ $notifiable->name }},
                            </p>
                            <p style="font-size:15px;color:#555;margin:0 0 10px;">
                                Has iniciado sesión en el sistema de gestión médica del Consultorio "El Chaparro de Guanta". Para completar el acceso, ingresa el siguiente código de verificación:
                            </p>

                            <div style="text-align:center;margin:30px 0;">
                                <div style="display:inline-block;background:#f0f4ff;border:2px dashed #667eea;border-radius:8px;padding:15px 35px;font-size:36px;font-weight:bold;letter-spacing:8px;color:#667eea;">
                                    {{ $code }}
                                </div>
                            </div>

                            <p style="font-size:14px;color:#888;margin:0 0 10px;">
                                Este código expirará en 10 minutos. Si no intentaste iniciar sesión, ignora este correo.
                            </p>
                        </td>
                    </tr>

                    <!-- INFORMACION DE SEGURIDAD -->
                    <tr>
                        <td style="padding:0 30px 20px;">
                            <div style="padding:12px;background-color:#f9f9f9;border-left:4px solid #667eea;border-radius:4px;font-size:13px;color:#666;">
                                <strong>🔒 Información de Seguridad:</strong> La verificación en dos pasos protege tu cuenta contra accesos no autorizados. Si tienes preguntas, contacta al administrador del sistema.
                            </div>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="padding:20px 30px;border-top:1px solid #e0e0e0;text-align:center;">
                            <p style="font-size:13px;color:#888;margin:3px 0;">Consultorio Popular Tipo III "El Chaparro de Guanta"</p>
                            <p style="font-size:12px;color:#999;margin:3px 0;">Sistema de Gestión de Historias Clínicas</p>
                            <p style="font-size:11px;color:#aaa;margin:3px 0;">© {{ date('Y') }} - Todos los derechos reservados</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
