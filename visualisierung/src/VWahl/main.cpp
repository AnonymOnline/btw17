#include <QApplication>
#include <QDesktopWidget>
#include <QLabel>
#include <iostream>
#include <QWindow>
#include <QScreen>
#include <QMessageBox>

#include "main.h"
#include "settingswindow.h"


/**
 * Main
 *
 * @brief main
 * @param argc
 * @param argv
 * @return
 */
int main(int argc, char *argv[])
{
    VWahl::VWahlApplication app(argc,argv);
    VWahl::run(app);
    return EXIT_SUCCESS;
    //app.exec();
}

namespace VWahl {


//Variables
QSettings *settings;
QVector<Database> *dbs;
Logger *log;
Database *db;

//Methods
/**
 * Run method of the program
 *
 * @brief run
 */
void run(QApplication& app)
{
    try
    {
        if(VWahl::init() != EXIT_SUCCESS)
        {
            Logger::log << L_ERROR << "Failed to initialize the program. This is an fatal error which will cause a shutdown." <<"\n";
            return;
        }

        Logger::log << L_INFO << "Running the application." << "\n";
        VWahl::showGui();
        Logger::log << L_INFO << "Initalized guis." << "\n";
        app.exec();
        Logger::log << L_INFO << "Stopping execution of app." << "\n";
    }catch(...)
    {
        //TO-DO: Advanced exception handling...
        Logger::log << L_ERROR << "A fatal error has occured!" << "\n";
        QMessageBox::warning(Q_NULLPTR,"Fehler!","Ein schwerer Fehler ist aufgetreten. Das Prog ramm wird beendet.",QMessageBox::Ok);
    }
    VWahl::shutdown();
}

/**
 * Will initialize the gui
 *
 * @brief showGuis
 */
void showGui()
{
    SettingsWindow *settingswindow = new SettingsWindow();
    settingswindow->showMaximized();
}

/**
 * Initializes the program
 *
 * @brief init
 * @return
 */
int init()
{
    //Initializing the logger

    Logger::log.init(Logger::All);

    //Initializing the settings
    if(initSettings()!= EXIT_SUCCESS)
    {
        Logger::log << L_ERROR << "Failed to initalize settings!" << "\n";
        return EXIT_FAILURE;
    }

    //Print all keys in settings-file
    Logger::log << L_DEBUG << "Settings for program...\n";
    for(QString key : VWahl::settings->allKeys())
        Logger::log << L_DEBUG << "key: " << key  << " value: " << VWahl::settings->value(key).toString() <<"\n";

    //Initializing databases
    db = new Database(VWahl::settings->value("database/type").toString(),
                      VWahl::settings->value("database/state").toString(),
                      VWahl::settings->value("database/year").toInt());


    //Open database connections
    if(db->connect() != EXIT_SUCCESS)
    {
        Logger::log << L_ERROR << "Failed to establish database connection!\n";
        Logger::log << L_ERROR << db->lastError().text().toStdString() << "\n";
        return EXIT_FAILURE;
    } else
         Logger::log << L_INFO << "opened Database!" << "\n";

    qRegisterMetaType<QList<Plots>>("QList<Plots>");

    Logger::log << L_INFO << "Initialized the program." << "\n";
    return EXIT_SUCCESS;
}

/**
 * shuts down the program
 *
 * @brief shutdown
 * @return
 */
int shutdown()
{
    Logger::log << L_INFO << "Shutting down the program." << "\n";

    VWahl::settings->sync();
    if (VWahl::settings->status() != 0){
        Logger::log << L_ERROR << "failed to write the settings to" << VWahl::settings->fileName().toStdString() << "\n";
        return EXIT_FAILURE;
    }
    else
        Logger::log << L_INFO << "wrote the settings to" << VWahl::settings->fileName().toStdString() << "\n";

    delete settings;

    db->close();
    delete db;

    return EXIT_SUCCESS;
}

int initSettings()
{
    settings = new QSettings("Evangelische_Schule_Neuruppin", "btw17");
    settings->setIniCodec("UTF-8");
    Logger::log << L_INFO << "reading config file from " << VWahl::settings->fileName() << "\n";
    settings->sync();
    return EXIT_SUCCESS;
}

}
